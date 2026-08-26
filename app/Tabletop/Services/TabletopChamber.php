<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Services;

use GreatMarketrealmTabletop\Tabletop\Exceptions\TabletopAccessDenied;
use GreatMarketrealmTabletop\Tabletop\Models\TabletopChamberState;
use GreatMarketrealmTabletop\Tabletop\Encounters\Contracts\EncounterRepository;
use GreatMarketrealmTabletop\Tabletop\Battle\Contracts\VitalityRepository;
use GreatMarketrealmTabletop\Tabletop\Battle\Contracts\DeathSaveRepository;
use GreatMarketrealmTabletop\Tabletop\Conditions\Contracts\ConditionRepository;
use GreatMarketrealmTabletop\Tables\Contracts\TableRepository;
use GreatMarketrealmTabletop\Tables\Memberships\Contracts\TableMembershipRepository;
use GreatMarketrealmTabletop\Tables\Memberships\Models\TableMember;
use GreatMarketrealmTabletop\Tables\Memberships\Models\TableMemberStatus;
use GreatMarketrealmTabletop\Tables\Scenes\Contracts\TableSceneRepository;
use GreatMarketrealmTabletop\Tables\Tokens\Contracts\TableTokenRepository;
use GreatMarketrealmTabletop\Tables\Tokens\Models\TableToken;
use RuntimeException;

defined('ABSPATH') || exit;

final class TabletopChamber
{
    public function __construct(
        private TableRepository $tables,
        private TableMembershipRepository $members,
        private TableSceneRepository $scenes,
        private TableTokenRepository $tokens,
        private EncounterRepository $encounters,
        private VitalityRepository $vitality,
        private DeathSaveRepository $deathSaves,
        private ConditionRepository $conditions
    ) {}

    public function state(
        string $tableId,
        int $viewerUserId
    ): TabletopChamberState {
        $table = $this->tables->find($tableId);

        if ($table === null) {
            throw new RuntimeException(
                'The requested Table could not be found.'
            );
        }

        $viewer = $this->members->find(
            $tableId,
            $viewerUserId
        );

        if (
            $viewer === null
            || $viewer->status()
                !== TableMemberStatus::ACTIVE
        ) {
            throw new TabletopAccessDenied(
                'Only active Table members may enter the Tabletop Chamber.'
            );
        }

        $members = array_map(
            static fn (TableMember $member): array =>
                $member->toArray(),
            $this->members->forTable($tableId)
        );

        $activeScene = null;

        foreach ($this->scenes->forTable($tableId) as $scene) {
            if ($scene->isActive()) {
                $activeScene = $scene;
                break;
            }
        }

        $tokens = [];

        if ($activeScene !== null) {
            foreach (
                $this->tokens->forScene(
                    $tableId,
                    $activeScene->id()
                )
                as $token
            ) {
                if (
                    ! $viewer->isDungeonMaster()
                    && ! $token->isVisible()
                ) {
                    continue;
                }

                $tokens[] = $token->toArray();
            }
        }

        $encounter = $activeScene !== null
            ? $this->encounters->currentForScene(
                $tableId,
                $activeScene->id()
            )
            : null;

        $vitality = [];
        $deathSaves = [];
        $conditions = [];

        foreach ($tokens as $token) {
            $tokenId = (string) ($token['id'] ?? '');

            if ($tokenId === '') {
                continue;
            }

            $vitality[$tokenId] = $this->vitality
                ->forToken(
                    $tableId,
                    $tokenId
                )
                ->toArray();

            $deathSaves[$tokenId] = $this->deathSaves
                ->forToken(
                    $tableId,
                    $tokenId
                )
                ->toArray();

            $conditions[$tokenId] = array_map(
                static fn ($condition): array =>
                    $condition->toArray(),
                $this->conditions->forToken(
                    $tableId,
                    $tokenId
                )
            );
        }

        return new TabletopChamberState(
            $table->toArray(),
            $viewer->toArray(),
            $members,
            $activeScene?->toArray(),
            $tokens,
            $encounter?->toArray(),
            $vitality,
            $deathSaves,
            $conditions
        );
    }
}
