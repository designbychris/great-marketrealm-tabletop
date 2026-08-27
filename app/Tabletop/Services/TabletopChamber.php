<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Services;

use GreatMarketrealmTabletop\Tabletop\Exceptions\TabletopAccessDenied;
use GreatMarketrealmTabletop\Tabletop\Models\TabletopChamberState;
use GreatMarketrealmTabletop\Tabletop\Encounters\Contracts\EncounterRepository;
use GreatMarketrealmTabletop\Tabletop\Battle\Contracts\VitalityRepository;
use GreatMarketrealmTabletop\Tabletop\Battle\Contracts\DeathSaveRepository;
use GreatMarketrealmTabletop\Tabletop\Conditions\Contracts\ConditionRepository;
use GreatMarketrealmTabletop\Tabletop\Battle\Contracts\BattleEventRepository;
use GreatMarketrealmTabletop\Tabletop\Battle\Presentation\BattleLogProjector;
use GreatMarketrealmTabletop\Tabletop\Presentation\CombatantStateProjector;
use GreatMarketrealmTabletop\Tabletop\Arsenal\Contracts\CombatArsenalRepository;
use GreatMarketrealmTabletop\Tabletop\Fog\Contracts\FogOfWarRepository;
use GreatMarketrealmTabletop\Tabletop\Fog\Services\FogOfWarProjector;
use GreatMarketrealmTabletop\Tabletop\Vision\Contracts\VisionBarrierRepository;
use GreatMarketrealmTabletop\Tables\Contracts\TableRepository;
use GreatMarketrealmTabletop\Tables\Memberships\Contracts\TableMembershipRepository;
use GreatMarketrealmTabletop\Tables\Memberships\Models\TableMember;
use GreatMarketrealmTabletop\Tables\Memberships\Models\TableMemberStatus;
use GreatMarketrealmTabletop\Tables\Scenes\Contracts\TableSceneRepository;
use GreatMarketrealmTabletop\Tables\Tokens\Contracts\TableTokenRepository;
use GreatMarketrealmTabletop\Tables\Tokens\Models\TableToken;
use GreatMarketrealmTabletop\Tables\Tokens\Models\TableTokenType;
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
        private ConditionRepository $conditions,
        private ?BattleEventRepository $battleEvents = null,
        private ?BattleLogProjector $battleLogProjector = null,
        private ?CombatantStateProjector $combatantStateProjector = null,
        private ?CombatArsenalRepository $arsenals = null,
        private ?FogOfWarRepository $fogRepository = null,
        private ?FogOfWarProjector $fogProjector = null,
        private ?VisionBarrierRepository $visionBarriers = null
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
        $tokenModels = [];
        $visionTokenModels = [];

        if ($activeScene !== null) {
            foreach (
                $this->tokens->forScene(
                    $tableId,
                    $activeScene->id()
                )
                as $token
            ) {
                if ($token->type() === TableTokenType::CHARACTER) {
                    $visionTokenModels[] = $token;
                }

                if (
                    ! $viewer->isDungeonMaster()
                    && ! $token->isVisible()
                ) {
                    continue;
                }

                $tokenModels[] = $token;
            }
        }

        $fog = [];
        $barrierModels = [];
        $visionLayer = [];

        if ($activeScene !== null && $this->visionBarriers !== null) {
            $barrierModels = $this->visionBarriers->forScene(
                $tableId,
                $activeScene->id()
            );

            if ($viewer->isDungeonMaster()) {
                $visionLayer = array_map(
                    static fn ($barrier): array => $barrier->toArray(),
                    $barrierModels
                );
            }
        }

        if (
            $activeScene !== null
            && $this->fogRepository !== null
            && $this->fogProjector !== null
        ) {
            $fog = $this->fogProjector->project(
                $activeScene,
                $this->fogRepository->forScene(
                    $tableId,
                    $activeScene->id()
                ),
                $visionTokenModels,
                $viewer->isDungeonMaster(),
                $barrierModels
            );
        }

        foreach ($tokenModels as $token) {
            if (
                ! $viewer->isDungeonMaster()
                && $activeScene !== null
                && $this->fogProjector !== null
                && ! $this->fogProjector
                    ->tokenIsCurrentlyVisible(
                        $activeScene,
                        $token,
                        $fog
                    )
            ) {
                continue;
            }

            $tokens[] = $token->toArray();
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
        $combatantStates = [];
        $arsenals = [];

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

            if ($this->combatantStateProjector !== null) {
                $combatantStates[$tokenId]
                    = $this->combatantStateProjector->project(
                        $token,
                        $vitality[$tokenId],
                        $deathSaves[$tokenId]
                    );
            }

            if ($this->arsenals !== null) {
                $arsenals[$tokenId] = $this->arsenals
                    ->forToken($tableId, $tokenId)
                    ->toArray();
            }
        }

        $battleLog = [];

        if (
            $encounter !== null
            && $this->battleEvents !== null
            && $this->battleLogProjector !== null
        ) {
            $labels = [];

            foreach ($tokens as $token) {
                $tokenId = (string) (
                    $token['id'] ?? ''
                );

                if ($tokenId !== '') {
                    $labels[$tokenId] = (string) (
                        $token['label']
                        ?? 'Combatant'
                    );
                }
            }

            $battleLog = $this->battleLogProjector
                ->project(
                    $this->battleEvents->forEncounter(
                        $tableId,
                        $encounter->id()
                    ),
                    $labels
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
            $conditions,
            $battleLog,
            $combatantStates,
            $arsenals,
            $fog,
            $visionLayer
        );
    }
}
