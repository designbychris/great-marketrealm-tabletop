<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Battle\Services;

use GreatMarketrealmTabletop\Tabletop\Battle\Contracts\BattleEventRepository;
use GreatMarketrealmTabletop\Tabletop\Battle\Contracts\VitalityRepository;
use GreatMarketrealmTabletop\Tabletop\Battle\Exceptions\BattleDeedDenied;
use GreatMarketrealmTabletop\Tabletop\Battle\Models\BattleDeed;
use GreatMarketrealmTabletop\Tabletop\Battle\Models\BattleEvent;
use GreatMarketrealmTabletop\Tabletop\Encounters\Contracts\EncounterRepository;
use GreatMarketrealmTabletop\Tabletop\Conditions\Contracts\ConditionRepository;
use GreatMarketrealmTabletop\Tabletop\Conditions\Services\ConditionCombatRules;
use GreatMarketrealmTabletop\Tabletop\Encounters\Exceptions\StaleEncounterRevision;
use GreatMarketrealmTabletop\Tables\Contracts\TableClock;
use GreatMarketrealmTabletop\Tables\Memberships\Contracts\TableMembershipRepository;
use GreatMarketrealmTabletop\Tables\Tokens\Contracts\TableTokenRepository;
use RuntimeException;

defined('ABSPATH') || exit;

final class BattleDeedManager
{
    public function __construct(
        private EncounterRepository $encounters,
        private TableMembershipRepository $members,
        private TableTokenRepository $tokens,
        private BattleEventRepository $events,
        private TableClock $clock,
        private ?VitalityRepository $vitality = null,
        private ?ConditionRepository $conditions = null,
        private ?ConditionCombatRules $conditionRules = null
    ) {}

    public function perform(
        string $tableId,
        int $viewerUserId,
        string $encounterId,
        string $deed,
        int $expectedRevision
    ): array {
        $deed = BattleDeed::assert($deed);
        $encounter = $this->encounters->find(
            $tableId,
            $encounterId
        );

        if ($encounter === null) {
            throw new RuntimeException(
                'The requested Encounter could not be found.'
            );
        }

        if ($encounter->revision() !== $expectedRevision) {
            throw new StaleEncounterRevision(
                'The Encounter changed before this deed was applied.'
            );
        }

        $combatant = $encounter->currentCombatant();

        if ($combatant === null) {
            throw new BattleDeedDenied(
                'No combatant currently has the turn.'
            );
        }

        $token = $this->tokens->find(
            $tableId,
            $combatant->tokenId()
        );

        if ($token === null) {
            throw new RuntimeException(
                'The current combatant token could not be found.'
            );
        }

        $member = $this->members->find(
            $tableId,
            $viewerUserId
        );

        if ($member === null || ! $member->isActive()) {
            throw new BattleDeedDenied(
                'Only an active Table member may perform a deed.'
            );
        }

        $mayAct = $member->isDungeonMaster()
            || (
                $token->controllerUserId() === $viewerUserId
                && $member->companionCharacterId() !== null
                && $token->sourceReference()
                    === $member->companionCharacterId()
            );

        if (! $mayAct) {
            throw new BattleDeedDenied(
                'This Table member does not control the current combatant.'
            );
        }

        if ($this->vitality !== null) {
            $vitality = $this->vitality->forToken(
                $tableId,
                $token->id()
            );

            if ($vitality->currentHp() === 0) {
                throw new BattleDeedDenied(
                    'A downed combatant cannot perform ordinary battle deeds.'
                );
            }
        }

        if (
            $this->conditions !== null
            && $this->conditionRules !== null
            && $this->conditionRules->blocksBattleDeeds(
                $this->conditions->forToken(
                    $tableId,
                    $token->id()
                )
            )
        ) {
            throw new BattleDeedDenied(
                'A stunned combatant cannot perform battle deeds.'
            );
        }

        $resource = BattleDeed::resource($deed);
        $encounter->spendTurnResource($resource);

        $event = new BattleEvent(
            bin2hex(random_bytes(12)),
            $tableId,
            $encounterId,
            'deed-performed',
            $token->id(),
            $encounter->round(),
            $encounter->turnIndex(),
            $this->clock->now(),
            [
                'deed' => $deed,
                'resource' => $resource,
            ]
        );

        $this->encounters->save($encounter);
        $this->events->append($event);

        return [
            'encounter' => $encounter,
            'event' => $event,
        ];
    }
}
