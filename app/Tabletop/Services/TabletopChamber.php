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
use GreatMarketrealmTabletop\Tables\Memberships\Contracts\TableMemberIdentityDirectory;
use GreatMarketrealmTabletop\Tables\Memberships\Presentation\TableMemberProjector;
use GreatMarketrealmTabletop\Integration\Companion\CompanionGateway;
use GreatMarketrealmTabletop\Integration\Companion\CompanionCharacterGateway;
use GreatMarketrealmTabletop\Tabletop\Chronicle\Contracts\ChamberChronicleRepository;
use GreatMarketrealmTabletop\Tabletop\Chronicle\Presentation\ChamberChronicleProjector;
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
        private ?VisionBarrierRepository $visionBarriers = null,
        private ?TableMemberIdentityDirectory $identities = null,
        private ?CompanionGateway $companion = null,
        private ?ChamberChronicleRepository $chamberEvents = null,
        private ?ChamberChronicleProjector $chamberChronicleProjector = null
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

        $memberModels = array_values(array_filter(
            $this->members->forTable($tableId),
            static fn (TableMember $member): bool =>
                $member->status() !== TableMemberStatus::LEFT
        ));
        $projector = $this->identities !== null
            ? new TableMemberProjector($this->identities)
            : null;
        $members = array_map(
            static fn (TableMember $member): array => $projector !== null
                ? $projector->project($member)
                : $member->toArray(),
            $memberModels
        );

        if ($this->companion instanceof CompanionCharacterGateway) {
            foreach ($members as $index => $memberProjection) {
                $memberUserId = (int) ($memberProjection['user_id'] ?? 0);
                $memberCharacterId = trim((string) ($memberProjection['companion_character_id'] ?? ''));

                if ($memberUserId < 1 || $memberCharacterId === '') {
                    continue;
                }

                $members[$index]['companion_character'] = $this->companion->characterForUser(
                    $memberUserId,
                    $memberCharacterId
                );
            }
        }

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

            $projection = $token->toArray();
            if (
                $this->companion instanceof CompanionCharacterGateway
                && $token->type() === TableTokenType::CHARACTER
                && $token->sourceReference() !== null
                && ($token->controllerUserId() ?? 0) > 0
            ) {
                $projection['companion_character'] = $this->companion->characterForUser(
                    (int) $token->controllerUserId(),
                    (string) $token->sourceReference()
                );
            }
            $controllerId = (int) ($projection['controller_user_id'] ?? 0);
            foreach ($members as $memberProjection) {
                if ((int) ($memberProjection['user_id'] ?? 0) === $controllerId) {
                    $projection['table_colour'] = (string) ($memberProjection['table_colour'] ?? '');
                    $projection['table_colour_hex'] = (string) ($memberProjection['table_colour_hex'] ?? '');
                    break;
                }
            }
            $tokens[] = $projection;
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
            $owners = [];

            foreach ($tokens as $token) {
                $tokenId = (string) (
                    $token['id'] ?? ''
                );

                if ($tokenId !== '') {
                    $owners[$tokenId] = (int) ($token['controller_user_id'] ?? 0);
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
                    $labels,
                    $owners
                );
        }

        $chamberLog = [];
        if ($this->chamberEvents !== null && $this->chamberChronicleProjector !== null) {
            $chamberLog = $this->chamberChronicleProjector->project(
                $this->chamberEvents->forTable($tableId)
            );
        }

        $coloursByUser = [];
        foreach ($members as $memberProjection) {
            $coloursByUser[(int) ($memberProjection['user_id'] ?? 0)] = [
                'key' => (string) ($memberProjection['table_colour'] ?? ''),
                'hex' => (string) ($memberProjection['table_colour_hex'] ?? ''),
            ];
        }
        foreach ($battleLog as $i => $entry) { $battleLog[$i]['table_colour'] = $coloursByUser[(int)($entry['user_id'] ?? 0)] ?? null; }
        foreach ($chamberLog as $i => $entry) { $chamberLog[$i]['table_colour'] = $coloursByUser[(int)($entry['user_id'] ?? 0)] ?? null; }

        return new TabletopChamberState(
            $table->toArray(),
            array_merge($viewer->toArray(), ['table_colour' => $viewer->tableColour(), 'table_colour_hex' => \GreatMarketrealmTabletop\Tables\Memberships\Models\TableColourPalette::hex($viewer->tableColour())]),
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
            $visionLayer,
            [
                'companion' => [
                    'available' => $this->companion?->available() ?? false,
                    'version' => $this->companion?->version(),
                    'characters' => $this->companion instanceof CompanionCharacterGateway
                        ? $this->companion->charactersForUser($viewerUserId)
                        : [],
                    'selected_character' => $this->companion instanceof CompanionCharacterGateway
                        && $viewer->companionCharacterId() !== null
                        ? $this->companion->characterForUser(
                            $viewerUserId,
                            $viewer->companionCharacterId()
                        )
                        : null,
                ],
            ],
            $chamberLog
        );
    }
}
