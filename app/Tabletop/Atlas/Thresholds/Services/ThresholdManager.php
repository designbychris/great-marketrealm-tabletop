<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Atlas\Thresholds\Services;

use GreatMarketrealmTabletop\Integration\Companion\CompanionCharacterGateway;
use GreatMarketrealmTabletop\Tables\Memberships\Contracts\TableMembershipRepository;
use GreatMarketrealmTabletop\Tables\Memberships\Models\TableMemberRole;
use GreatMarketrealmTabletop\Tables\Memberships\Models\TableMemberStatus;
use GreatMarketrealmTabletop\Tables\Scenes\Contracts\TableSceneRepository;
use GreatMarketrealmTabletop\Tables\Scenes\Models\TableScene;
use GreatMarketrealmTabletop\Tables\Tokens\Contracts\TableTokenRepository;
use GreatMarketrealmTabletop\Tables\Tokens\Models\TableTokenType;
use GreatMarketrealmTabletop\Tables\Tokens\Services\TableTokenManager;
use GreatMarketrealmTabletop\Tabletop\Atlas\Exceptions\AtlasDenied;
use GreatMarketrealmTabletop\Tabletop\Atlas\Thresholds\Contracts\ThresholdRepository;
use GreatMarketrealmTabletop\Tabletop\Atlas\Thresholds\Models\ThresholdMarker;
use GreatMarketrealmTabletop\Tabletop\Atlas\Thresholds\Models\ThresholdType;
use DateTimeImmutable;

defined('ABSPATH') || exit;

final class ThresholdManager
{
    public function __construct(
        private TableMembershipRepository $members,
        private TableSceneRepository $scenes,
        private ThresholdRepository $thresholds,
        private TableTokenRepository $tokens,
        private TableTokenManager $tokenManager,
        private CompanionCharacterGateway $companion
    ) {}

    public function place(
        string $tableId,
        int $viewerUserId,
        string $sceneId,
        string $type,
        float $x,
        float $y
    ): ThresholdMarker {
        $this->assertDungeonMaster($tableId, $viewerUserId);
        $scene = $this->requiredScene($tableId, $sceneId);
        $scene->coordinates($x, $y);

        $marker = ThresholdMarker::create(
            function_exists('wp_generate_uuid4') ? wp_generate_uuid4() : uniqid('threshold-', true),
            $tableId,
            $sceneId,
            ThresholdType::assert($type),
            $x,
            $y,
            new DateTimeImmutable('now')
        );
        $this->thresholds->save($marker);
        return $marker;
    }

    public function move(
        string $tableId,
        int $viewerUserId,
        string $sceneId,
        string $markerId,
        float $x,
        float $y
    ): ThresholdMarker {
        $this->assertDungeonMaster($tableId, $viewerUserId);
        $scene = $this->requiredScene($tableId, $sceneId);
        $scene->coordinates($x, $y);

        $existing = $this->thresholds->find($tableId, $sceneId, $markerId);
        if ($existing === null) {
            throw new AtlasDenied('That Threshold Marker could not be found.');
        }

        $marker = ThresholdMarker::create(
            $existing->id(),
            $existing->tableId(),
            $existing->sceneId(),
            $existing->type(),
            $x,
            $y,
            $existing->createdAt()
        );
        $this->thresholds->save($marker);

        return $marker;
    }

    public function remove(string $tableId, int $viewerUserId, string $sceneId, string $markerId): void
    {
        $this->assertDungeonMaster($tableId, $viewerUserId);
        $this->requiredScene($tableId, $sceneId);
        if ($this->thresholds->find($tableId, $sceneId, $markerId) === null) {
            throw new AtlasDenied('That Threshold Marker could not be found.');
        }
        $this->thresholds->delete($tableId, $sceneId, $markerId);
    }

    /** @return array<int,ThresholdMarker> */
    public function markersForDungeonMaster(string $tableId, int $viewerUserId, string $sceneId): array
    {
        $this->assertDungeonMaster($tableId, $viewerUserId);
        $this->requiredScene($tableId, $sceneId);
        return $this->thresholds->forScene($tableId, $sceneId);
    }

    /**
     * Forge only missing player-character tokens in the destination Scene.
     * Existing destination tokens keep their remembered positions.
     *
     * @return array<int,\GreatMarketrealmTabletop\Tables\Tokens\Models\TableToken>
     */
    public function welcomeParty(string $tableId, TableScene $scene): array
    {
        $partyThresholds = array_values(array_filter(
            $this->thresholds->forScene($tableId, $scene->id()),
            static fn (ThresholdMarker $marker): bool => $marker->type() === ThresholdType::PARTY
        ));
        if ($partyThresholds === []) {
            return [];
        }

        $existing = $this->tokens->forScene($tableId, $scene->id());
        $created = [];
        $arrivalIndex = 0;

        foreach ($this->members->forTable($tableId) as $member) {
            if (
                $member->status() !== TableMemberStatus::ACTIVE
                || $member->role() !== TableMemberRole::PLAYER
                || $member->companionCharacterId() === null
            ) {
                continue;
            }

            $characterId = (string) $member->companionCharacterId();
            $alreadyPresent = false;
            foreach ($existing as $token) {
                if (
                    $token->type() === TableTokenType::CHARACTER
                    && $token->controllerUserId() === $member->userId()
                    && (string) ($token->sourceReference() ?? '') === $characterId
                ) {
                    $alreadyPresent = true;
                    break;
                }
            }
            if ($alreadyPresent) {
                continue;
            }

            $character = $this->companion->characterForUser($member->userId(), $characterId);
            if ($character === null) {
                continue;
            }

            $marker = $partyThresholds[$arrivalIndex % count($partyThresholds)];
            $point = $this->arrivalPoint($scene, $marker, intdiv($arrivalIndex, count($partyThresholds)));
            $token = $this->tokenManager->place(
                $tableId,
                $scene->id(),
                (string) ($character['name'] ?? 'Adventurer'),
                TableTokenType::CHARACTER,
                $characterId,
                $member->userId(),
                $point['x'],
                $point['y']
            );
            $existing[] = $token;
            $created[] = $token;
            ++$arrivalIndex;
        }

        return $created;
    }

    /** @return array{x:float,y:float} */
    private function arrivalPoint(TableScene $scene, ThresholdMarker $marker, int $ringIndex): array
    {
        $offsets = [[0,0],[1,0],[-1,0],[0,1],[0,-1],[1,1],[-1,1],[1,-1],[-1,-1]];
        [$dx, $dy] = $offsets[$ringIndex % count($offsets)];
        $ring = intdiv($ringIndex, count($offsets)) + 1;
        if ($ringIndex === 0) {
            $ring = 0;
        }
        $stepX = $scene->width() > 0 ? $scene->gridSize() / $scene->width() : 0.02;
        $stepY = $scene->height() > 0 ? $scene->gridSize() / $scene->height() : 0.02;

        return [
            'x' => max(0, min(1, $marker->x() + ($dx * $stepX * $ring))),
            'y' => max(0, min(1, $marker->y() + ($dy * $stepY * $ring))),
        ];
    }

    private function requiredScene(string $tableId, string $sceneId): TableScene
    {
        $scene = $this->scenes->find($tableId, trim($sceneId));
        if ($scene === null) {
            throw new AtlasDenied('That Scene could not be found in the Keeper\'s Atlas.');
        }
        return $scene;
    }

    private function assertDungeonMaster(string $tableId, int $viewerUserId): void
    {
        $member = $this->members->find($tableId, $viewerUserId);
        if (
            $member === null
            || $member->status() !== TableMemberStatus::ACTIVE
            || $member->role() !== TableMemberRole::DUNGEON_MASTER
        ) {
            throw new AtlasDenied('Only the Dungeon Master may set Threshold Markers.');
        }
    }
}
