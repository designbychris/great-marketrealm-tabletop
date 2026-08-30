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
use GreatMarketrealmTabletop\Tabletop\Battlefield\Services\BattlefieldMeasure;
use GreatMarketrealmTabletop\Tabletop\Battle\Models\AttackRollMode;
use GreatMarketrealmTabletop\Tables\Scenes\Contracts\TableSceneRepository;
use GreatMarketrealmTabletop\Tabletop\Arsenal\Contracts\CombatArsenalRepository;
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
        private ?ConditionCombatRules $conditionRules = null,
        private ?TableSceneRepository $scenes = null,
        private ?BattlefieldMeasure $battlefield = null,
        private ?AttackRangeResolver $rangeResolver = null,
        private ?CombatArsenalRepository $arsenals = null
    ) {}

    /** @return array<string,mixed> */
    public function attack(
        string $tableId,
        int $viewerUserId,
        string $encounterId,
        string $targetTokenId,
        int $expectedRevision,
        ?string $attackId = null,
        bool $autoResolveDamage = true
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

        $selectedAttack = null;

        if ($attackId !== null && $attackId !== '' && $this->arsenals !== null) {
            $selectedAttack = $this->arsenals
                ->forToken($tableId, $attacker->id())
                ->find($attackId);

            if ($selectedAttack === null) {
                throw new AttackDenied(
                    'The selected attack is not in this combatant Arsenal.'
                );
            }
        }

        $attackerProfile = $selectedAttack?->combat()
            ?? $this->profiles->forToken($tableId, $attacker->id());
        $damageProfile = $selectedAttack?->damage()
            ?? $this->damageProfiles->forToken($tableId, $attacker->id());
        $targetProfile = $this->profiles->forToken(
            $tableId,
            $target->id()
        );

        $distance = null;
        $range = null;

        if (
            $this->scenes !== null
            && $this->battlefield !== null
            && $this->rangeResolver !== null
        ) {
            $scene = $this->scenes->find(
                $tableId,
                $attacker->sceneId()
            );

            if ($scene === null) {
                throw new AttackDenied(
                    'The active battlefield could not be measured.'
                );
            }

            $distance = $this->battlefield->between(
                $scene,
                $attacker,
                $target
            );

            $range = $this->rangeResolver->assess(
                $distance->feet(),
                $attackerProfile
            );

            if (! $range->inRange()) {
                throw new AttackDenied(
                    sprintf(
                        'Out of range: target is %d ft away; this attack reaches %d ft.',
                        $distance->feet(),
                        $attackerProfile->longRangeFeet()
                    )
                );
            }
        }

        $deed = $this->deeds->perform(
            $tableId,
            $viewerUserId,
            $encounterId,
            BattleDeed::ATTACK,
            $expectedRevision
        );

        $advantage = false;
        $disadvantage = $range !== null
            && $range->longRange();

        if (
            $this->conditions !== null
            && $this->conditionRules !== null
        ) {
            $factors = $this->conditionRules
                ->attackRollFactors(
                    $this->conditions->forToken(
                        $tableId,
                        $attacker->id()
                    ),
                    $this->conditions->forToken(
                        $tableId,
                        $target->id()
                    ),
                    $distance?->feet()
                );

            $advantage = $factors['advantage'];
            $disadvantage = $disadvantage
                || $factors['disadvantage'];
        }

        $rollMode = AttackRollMode::fromFactors(
            $advantage,
            $disadvantage
        );

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
                'attack_id' => $selectedAttack?->id(),
                'attack_name' => $selectedAttack?->name(),
                'attack_kind' => $selectedAttack?->kind(),
                'targeting' => $range?->toArray(),
                'damage_profile' => $damageProfile->toArray(),
            ] + $outcome->toArray()
        );

        $this->events->append($event);

        $damageRoll = null;
        $damageEvent = null;
        $damageAdjustment = null;
        $vitality = null;

        if ($outcome->isHit() && $autoResolveDamage) {
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
            'selected_attack' => $selectedAttack,
            'targeting' => $range,
            'damage' => $damageRoll,
            'damage_adjustment' => $damageAdjustment,
            'vitality' => $vitality,
            'death_saves' => isset($deathSaveState)
                ? $deathSaveState
                : null,
            'pending_damage' => $outcome->isHit() && ! $autoResolveDamage
                ? [
                    'attack_event_id' => $event->toArray()['id'],
                    'attack_name' => $selectedAttack?->name() ?? 'Attack',
                    'damage_profile' => $damageProfile->toArray(),
                    'critical' => $outcome->result() === \GreatMarketrealmTabletop\Tabletop\Battle\Models\AttackOutcome::CRITICAL_HIT,
                ]
                : null,
        ];
    }
}
