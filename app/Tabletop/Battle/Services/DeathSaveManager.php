<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Battle\Services;

use GreatMarketrealmTabletop\Tabletop\Battle\Contracts\BattleEventRepository;
use GreatMarketrealmTabletop\Tabletop\Battle\Contracts\DeathSaveRepository;
use GreatMarketrealmTabletop\Tabletop\Battle\Contracts\VitalityRepository;
use GreatMarketrealmTabletop\Tabletop\Battle\Exceptions\DeathSaveDenied;
use GreatMarketrealmTabletop\Tabletop\Battle\Models\BattleEvent;
use GreatMarketrealmTabletop\Tabletop\Battle\Models\DeathSaveOutcome;
use GreatMarketrealmTabletop\Tabletop\Encounters\Contracts\EncounterRepository;
use GreatMarketrealmTabletop\Tables\Contracts\TableClock;
use GreatMarketrealmTabletop\Tables\Memberships\Contracts\TableMembershipRepository;
use GreatMarketrealmTabletop\Tables\Tokens\Contracts\TableTokenRepository;
use RuntimeException;

defined('ABSPATH') || exit;

final class DeathSaveManager
{
    public function __construct(
        private EncounterRepository $encounters,
        private TableMembershipRepository $members,
        private TableTokenRepository $tokens,
        private VitalityRepository $vitality,
        private DeathSaveRepository $deathSaves,
        private BattleEventRepository $events,
        private DeathSaveResolver $resolver,
        private TableClock $clock
    ) {}

    /** @return array<string,mixed> */
    public function roll(
        string $tableId,
        int $viewerUserId,
        string $encounterId,
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

        if ($encounter->revision() !== $expectedRevision) {
            throw new DeathSaveDenied(
                'The Encounter changed before this death save.'
            );
        }

        $combatant = $encounter->currentCombatant();

        if ($combatant === null) {
            throw new DeathSaveDenied(
                'No combatant currently has the turn.'
            );
        }

        $token = $this->tokens->find(
            $tableId,
            $combatant->tokenId()
        );

        if ($token === null) {
            throw new DeathSaveDenied(
                'The current combatant token could not be found.'
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
            throw new DeathSaveDenied(
                'Only an active Table member may roll a death save.'
            );
        }

        if (
            ! $member->isDungeonMaster()
            && $token->controllerUserId() !== $viewerUserId
        ) {
            throw new DeathSaveDenied(
                'Only the controlling Player may roll this death save.'
            );
        }

        $vitality = $this->vitality->forToken(
            $tableId,
            $token->id()
        );

        if ($vitality->currentHp() > 0) {
            throw new DeathSaveDenied(
                'Death saves are only available at 0 HP.'
            );
        }

        $state = $this->deathSaves->forToken(
            $tableId,
            $token->id()
        );

        if ($state->resolved()) {
            throw new DeathSaveDenied(
                'This death-save sequence is already resolved.'
            );
        }

        $outcome = $this->resolver->resolve();

        if ($outcome->revives()) {
            $vitality->reviveAtOneHp();
            $state->reset();

            $this->vitality->save(
                $tableId,
                $vitality
            );
        } else {
            $state->recordSuccess(
                $outcome->successes()
            );
            $state->recordFailure(
                $outcome->failures()
            );
        }

        $this->deathSaves->save(
            $tableId,
            $state
        );

        $event = new BattleEvent(
            bin2hex(random_bytes(12)),
            $tableId,
            $encounterId,
            'death-save-resolved',
            $token->id(),
            $encounter->round(),
            $encounter->turnIndex(),
            $this->clock->now(),
            [
                'outcome' => $outcome->toArray(),
                'death_saves' => $state->toArray(),
                'vitality' => $vitality->toArray(),
            ]
        );

        $this->events->append($event);

        return [
            'encounter' => $encounter,
            'outcome' => $outcome,
            'death_saves' => $state,
            'vitality' => $vitality,
            'event' => $event,
        ];
    }
}
