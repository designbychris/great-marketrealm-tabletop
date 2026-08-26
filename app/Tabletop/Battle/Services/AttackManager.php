<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Battle\Services;

use GreatMarketrealmTabletop\Tabletop\Battle\Contracts\BattleEventRepository;
use GreatMarketrealmTabletop\Tabletop\Battle\Contracts\CombatProfileRepository;
use GreatMarketrealmTabletop\Tabletop\Battle\Contracts\DamageProfileRepository;
use GreatMarketrealmTabletop\Tabletop\Battle\Contracts\DamageDefenseRepository;
use GreatMarketrealmTabletop\Tabletop\Battle\Contracts\DeathSaveRepository;
use GreatMarketrealmTabletop\Tabletop\Battle\Contracts\VitalityRepository;
use GreatMarketrealmTabletop\Tabletop\Battle\Exceptions\AttackDenied;
use GreatMarketrealmTabletop\Tabletop\Battle\Models\BattleDeed;
use GreatMarketrealmTabletop\Tabletop\Battle\Models\BattleEvent;
use GreatMarketrealmTabletop\Tabletop\Encounters\Contracts\EncounterRepository;
use GreatMarketrealmTabletop\Tabletop\Conditions\Contracts\ConditionRepository;
use GreatMarketrealmTabletop\Tabletop\Conditions\Services\ConditionCombatRules;
use GreatMarketrealmTabletop\Tables\Contracts\TableClock;
use GreatMarketrealmTabletop\Tables\Memberships\Contracts\TableMembershipRepository;
use GreatMarketrealmTabletop\Tables\Tokens\Contracts\TableTokenRepository;
use RuntimeException;

defined('ABSPATH') || exit;

final class AttackManager
{
    public function __construct(
        private BattleDeedManager $deeds,
        private EncounterRepository $encounters,
        private TableMembershipRepository $members,
        private TableTokenRepository $tokens,
        private CombatProfileRepository $profiles,
        private DamageProfileRepository $damageProfiles,
        private DamageDefenseRepository $damageDefenses,
        private VitalityRepository $vitality,
        private DeathSaveRepository $deathSaves,
        private BattleEventRepository $events,
        private AttackResolver $resolver,
        private DamageResolver $damageResolver,
        private DamageDefenseResolver $defenseResolver,
        private TableClock $clock,
        private ?ConditionRepository $conditions = null,
        private ?ConditionCombatRules $conditionRules = null
    ) {}

    /** @return array<string,mixed> */
    public function attack(
        string $tableId,
        int $viewerUserId,
        string $encounterId,
        string $targetTokenId,
        int $expectedRevision
    ): array {
        $encounter = $this->encounters->find(
            $tableId,
            $encounterId
        );

        if ($encounter === null) {
            throw new RuntimeException(
                'The requested Encounter could not be found.'
            );
        }

        $combatant = $encounter->currentCombatant();

        if ($combatant === null) {
            throw new AttackDenied(
                'No combatant currently has the turn.'
            );
        }

        $attacker = $this->tokens->find(
            $tableId,
            $combatant->tokenId()
        );
        $target = $this->tokens->find(
            $tableId,
            $targetTokenId
        );

        if ($attacker === null || $target === null) {
            throw new AttackDenied(
                'Both attacker and target must exist at this Table.'
            );
        }

        if ($attacker->id() === $target->id()) {
            throw new AttackDenied(
                'A combatant cannot target itself with this attack.'
            );
        }

        if ($attacker->sceneId() !== $target->sceneId()) {
            throw new AttackDenied(
                'Attack targets must share the active Scene.'
            );
        }

        $member = $this->members->find(
            $tableId,
            $viewerUserId
        );

        if (
            $member === null
            || ! $member->isActive()
        ) {
            throw new AttackDenied(
                'Only an active Table member may attack.'
            );
        }

        if (
            ! $member->isDungeonMaster()
            && ! $target->isVisible()
        ) {
            throw new AttackDenied(
                'That target is hidden from this Player.'
            );
        }

        $attackerProfile = $this->profiles->forToken(
            $tableId,
            $attacker->id()
        );
        $targetProfile = $this->profiles->forToken(
            $tableId,
            $target->id()
        );

        $deed = $this->deeds->perform(
            $tableId,
            $viewerUserId,
            $encounterId,
            BattleDeed::ATTACK,
            $expectedRevision
        );

        $rollMode = 'normal';

        if (
            $this->conditions !== null
            && $this->conditionRules !== null
        ) {
            $rollMode = $this->conditionRules
                ->attackRollMode(
                    $this->conditions->forToken(
                        $tableId,
                        $attacker->id()
                    ),
                    $this->conditions->forToken(
                        $tableId,
                        $target->id()
                    )
                );
        }

        $outcome = $this->resolver->resolve(
            $attackerProfile,
            $targetProfile,
            $rollMode
        );

        $updatedEncounter = $deed['encounter'];
        $event = new BattleEvent(
            bin2hex(random_bytes(12)),
            $tableId,
            $encounterId,
            'attack-resolved',
            $attacker->id(),
            $updatedEncounter->round(),
            $updatedEncounter->turnIndex(),
            $this->clock->now(),
            [
                'deed' => BattleDeed::ATTACK,
                'target_token_id' => $target->id(),
            ] + $outcome->toArray()
        );

        $this->events->append($event);

        $damageRoll = null;
        $damageEvent = null;
        $damageAdjustment = null;
        $vitality = null;

        if ($outcome->isHit()) {
            $damageProfile = $this->damageProfiles->forToken(
                $tableId,
                $attacker->id()
            );

            $damageRoll = $this->damageResolver->resolve(
                $damageProfile,
                $outcome->result()
                    === \GreatMarketrealmTabletop\Tabletop\Battle\Models\AttackOutcome::CRITICAL_HIT
            );

            $damageAdjustment = $this->defenseResolver->resolve(
                $damageRoll->total(),
                $damageProfile->damageType(),
                $this->damageDefenses->forToken(
                    $tableId,
                    $target->id()
                )
            );

            $vitality = $this->vitality->forToken(
                $tableId,
                $target->id()
            );
            $hpBefore = $vitality->currentHp();

            $application = $vitality->damage(
                $damageAdjustment->resolvedDamage()
            );

            $deathSaveState = $this->deathSaves->forToken(
                $tableId,
                $target->id()
            );
            $deathConsequence = 'none';

            if ($hpBefore === 0) {
                $failureCount = $outcome->result()
                    === \GreatMarketrealmTabletop\Tabletop\Battle\Models\AttackOutcome::CRITICAL_HIT
                    ? 2
                    : 1;

                $deathSaveState->recordDamageFailure(
                    $failureCount
                );
                $deathConsequence = $failureCount === 2
                    ? 'critical-damage-failures'
                    : 'damage-failure';
            } elseif (
                $vitality->currentHp() === 0
                && (int) ($application['excess_damage'] ?? 0)
                    >= $vitality->maximumHp()
            ) {
                $deathSaveState->markFallen();
                $deathConsequence = 'massive-damage';
            }

            $this->vitality->save(
                $tableId,
                $vitality
            );
            $this->deathSaves->save(
                $tableId,
                $deathSaveState
            );

            $damageEvent = new BattleEvent(
                bin2hex(random_bytes(12)),
                $tableId,
                $encounterId,
                'damage-applied',
                $attacker->id(),
                $updatedEncounter->round(),
                $updatedEncounter->turnIndex(),
                $this->clock->now(),
                [
                    'target_token_id' => $target->id(),
                    'damage' => $damageRoll->toArray(),
                    'damage_adjustment' => $damageAdjustment->toArray(),
                    'application' => $application,
                    'vitality' => $vitality->toArray(),
                    'death_consequence' => $deathConsequence,
                    'death_saves' => $deathSaveState->toArray(),
                ]
            );

            $this->events->append($damageEvent);
        }

        return [
            'encounter' => $updatedEncounter,
            'deed_event' => $deed['event'],
            'attack_event' => $event,
            'damage_event' => $damageEvent,
            'outcome' => $outcome,
            'damage' => $damageRoll,
            'damage_adjustment' => $damageAdjustment,
            'vitality' => $vitality,
            'death_saves' => isset($deathSaveState)
                ? $deathSaveState
                : null,
        ];
    }
}
