<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Encounters\Services;

use GreatMarketrealmTabletop\Tabletop\Encounters\Contracts\EncounterIdGenerator;
use GreatMarketrealmTabletop\Tabletop\Encounters\Contracts\EncounterRepository;
use GreatMarketrealmTabletop\Tabletop\Encounters\Exceptions\EncounterControlDenied;
use GreatMarketrealmTabletop\Tabletop\Encounters\Exceptions\EncounterStateException;
use GreatMarketrealmTabletop\Tabletop\Encounters\Exceptions\StaleEncounterRevision;
use GreatMarketrealmTabletop\Tabletop\Encounters\Models\Encounter;
use GreatMarketrealmTabletop\Tabletop\Encounters\Models\EncounterCombatant;
use GreatMarketrealmTabletop\Tabletop\Conditions\Services\ConditionLifecycle;
use GreatMarketrealmTabletop\Tables\Contracts\TableClock;
use GreatMarketrealmTabletop\Tables\Contracts\TableRepository;
use GreatMarketrealmTabletop\Tables\Memberships\Contracts\TableMembershipRepository;
use GreatMarketrealmTabletop\Tables\Models\TableStatus;
use GreatMarketrealmTabletop\Tables\Scenes\Contracts\TableSceneRepository;
use GreatMarketrealmTabletop\Tables\Tokens\Contracts\TableTokenRepository;
use RuntimeException;

defined('ABSPATH') || exit;

final class EncounterManager
{
    public function __construct(
        private TableRepository $tables,
        private TableMembershipRepository $members,
        private TableSceneRepository $scenes,
        private TableTokenRepository $tokens,
        private EncounterRepository $encounters,
        private EncounterIdGenerator $ids,
        private TableClock $clock,
        private EncounterControlPolicy $policy,
        private ?ConditionLifecycle $conditionLifecycle = null
    ) {}

    public function prepare(
        string $tableId,
        int $viewerUserId,
        string $sceneId,
        string $name
    ): Encounter {
        $this->requireDungeonMaster($tableId, $viewerUserId);
        $this->requireOpenTable($tableId);
        $scene = $this->requireScene($tableId, $sceneId);

        if (! $scene->isActive()) {
            throw new EncounterStateException(
                'An Encounter must be prepared on the active Scene.'
            );
        }

        if (
            $this->encounters->currentForScene(
                $tableId,
                $sceneId
            ) !== null
        ) {
            throw new EncounterStateException(
                'That Scene already has a current Encounter.'
            );
        }

        $encounter = Encounter::prepare(
            $this->ids->generate(),
            $tableId,
            $sceneId,
            $name,
            $this->clock->now()
        );

        $this->encounters->save($encounter);

        return $encounter;
    }

    public function addCombatant(
        string $tableId,
        int $viewerUserId,
        string $encounterId,
        string $tokenId,
        int $initiative,
        int $initiativeModifier,
        int $expectedRevision
    ): Encounter {
        $encounter = $this->controlledEncounter(
            $tableId,
            $viewerUserId,
            $encounterId,
            $expectedRevision
        );

        $token = $this->tokens->find($tableId, $tokenId);

        if (
            $token === null
            || $token->sceneId() !== $encounter->sceneId()
        ) {
            throw new EncounterStateException(
                'Combatants must be tokens on the Encounter Scene.'
            );
        }

        $encounter->addCombatant(
            new EncounterCombatant(
                $tokenId,
                $initiative,
                $initiativeModifier
            )
        );

        $this->encounters->save($encounter);
        return $encounter;
    }

    public function start(
        string $tableId,
        int $viewerUserId,
        string $encounterId,
        int $expectedRevision
    ): Encounter {
        return $this->mutate(
            $tableId,
            $viewerUserId,
            $encounterId,
            $expectedRevision,
            static fn (Encounter $encounter) => $encounter->start()
        );
    }

    public function pause(
        string $tableId,
        int $viewerUserId,
        string $encounterId,
        int $expectedRevision
    ): Encounter {
        return $this->mutate(
            $tableId,
            $viewerUserId,
            $encounterId,
            $expectedRevision,
            static fn (Encounter $encounter) => $encounter->pause()
        );
    }

    public function resume(
        string $tableId,
        int $viewerUserId,
        string $encounterId,
        int $expectedRevision
    ): Encounter {
        return $this->mutate(
            $tableId,
            $viewerUserId,
            $encounterId,
            $expectedRevision,
            static fn (Encounter $encounter) => $encounter->resume()
        );
    }

    public function advance(
        string $tableId,
        int $viewerUserId,
        string $encounterId,
        int $expectedRevision
    ): Encounter {
        $encounter = $this->controlledEncounter(
            $tableId,
            $viewerUserId,
            $encounterId,
            $expectedRevision
        );

        $scene = $this->requireScene(
            $tableId,
            $encounter->sceneId()
        );

        if (! $scene->isActive()) {
            throw new EncounterStateException(
                'The Encounter Scene is no longer active.'
            );
        }

        $outgoing = $encounter->currentCombatant();
        $encounter->advanceTurn();
        $this->encounters->save($encounter);

        if (
            $outgoing !== null
            && $this->conditionLifecycle !== null
        ) {
            $this->conditionLifecycle->turnEnded(
                $tableId,
                $encounter,
                $outgoing->tokenId()
            );
        }

        return $encounter;
    }

    public function end(
        string $tableId,
        int $viewerUserId,
        string $encounterId,
        int $expectedRevision
    ): Encounter {
        return $this->mutate(
            $tableId,
            $viewerUserId,
            $encounterId,
            $expectedRevision,
            static fn (Encounter $encounter) => $encounter->end()
        );
    }

    public function currentForActiveScene(string $tableId): ?Encounter
    {
        $table = $this->tables->find($tableId);

        if ($table === null) {
            return null;
        }

        foreach ($this->scenes->forTable($tableId) as $scene) {
            if (! $scene->isActive()) {
                continue;
            }

            $encounter = $this->encounters->currentForScene(
                $tableId,
                $scene->id()
            );

            if (
                $encounter !== null
                && $table->status() === TableStatus::ENDED
                && ! $encounter->isEnded()
            ) {
                $encounter->end();
                $this->encounters->save($encounter);
            }

            return $encounter;
        }

        return null;
    }

    /** @param callable(Encounter):void $mutation */
    private function mutate(
        string $tableId,
        int $viewerUserId,
        string $encounterId,
        int $expectedRevision,
        callable $mutation
    ): Encounter {
        $encounter = $this->controlledEncounter(
            $tableId,
            $viewerUserId,
            $encounterId,
            $expectedRevision
        );

        $scene = $this->requireScene(
            $tableId,
            $encounter->sceneId()
        );

        if (! $scene->isActive()) {
            throw new EncounterStateException(
                'The Encounter Scene is no longer active.'
            );
        }

        $mutation($encounter);
        $this->encounters->save($encounter);

        return $encounter;
    }

    private function controlledEncounter(
        string $tableId,
        int $viewerUserId,
        string $encounterId,
        int $expectedRevision
    ): Encounter {
        $this->requireDungeonMaster($tableId, $viewerUserId);
        $this->requireOpenTable($tableId);

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
                'The Encounter changed before this request was applied.'
            );
        }

        return $encounter;
    }

    private function requireDungeonMaster(
        string $tableId,
        int $viewerUserId
    ): void {
        $member = $this->members->find(
            $tableId,
            $viewerUserId
        );

        if (! $this->policy->mayControl($member)) {
            throw new EncounterControlDenied(
                'Only the active Dungeon Master may control an Encounter.'
            );
        }
    }

    private function requireOpenTable(string $tableId): void
    {
        $table = $this->tables->find($tableId);

        if ($table === null) {
            throw new RuntimeException(
                'The requested Table could not be found.'
            );
        }

        if ($table->status() === TableStatus::ENDED) {
            throw new EncounterStateException(
                'An ended Table cannot change Encounter state.'
            );
        }
    }

    private function requireScene(
        string $tableId,
        string $sceneId
    ): \GreatMarketrealmTabletop\Tables\Scenes\Models\TableScene {
        $scene = $this->scenes->find($tableId, $sceneId);

        if ($scene === null) {
            throw new RuntimeException(
                'The Encounter Scene could not be found.'
            );
        }

        return $scene;
    }
}
