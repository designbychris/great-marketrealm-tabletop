<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Atlas\Services;

defined('ABSPATH') || exit;

use GreatMarketrealmTabletop\Tables\Memberships\Contracts\TableMembershipRepository;
use GreatMarketrealmTabletop\Tables\Memberships\Models\TableMemberRole;
use GreatMarketrealmTabletop\Tables\Memberships\Models\TableMemberStatus;
use GreatMarketrealmTabletop\Tables\Scenes\Models\GridType;
use GreatMarketrealmTabletop\Tables\Scenes\Models\TableScene;
use GreatMarketrealmTabletop\Tables\Scenes\Services\TableSceneManager;
use GreatMarketrealmTabletop\Tabletop\Atlas\Exceptions\AtlasDenied;
use GreatMarketrealmTabletop\Tabletop\Cartography\Services\BattlemapInspector;
use GreatMarketrealmTabletop\Tabletop\Atlas\Thresholds\Models\ThresholdMarker;
use GreatMarketrealmTabletop\Tabletop\Atlas\Thresholds\Services\ThresholdManager;

final class KeepersAtlas
{
    public function __construct(
        private TableMembershipRepository $members,
        private TableSceneManager $scenes,
        private BattlemapInspector $images,
        private SceneShelfCleaner $cleaner,
        private ThresholdManager $thresholds
    ) {}

    public function addMap(
        string $tableId,
        int $viewerUserId,
        string $name,
        int $attachmentId,
        int $gridSize = 64
    ): TableScene {
        $this->assertDungeonMaster($tableId, $viewerUserId);

        $name = trim($name);
        if ($name === '') {
            throw new AtlasDenied('Give this place a name before adding it to the Atlas.');
        }

        $image = $this->images->inspect($attachmentId);

        return $this->scenes->create(
            $tableId,
            $name,
            $image->attachmentId(),
            $image->width(),
            $image->height(),
            GridType::SQUARE,
            max(1, $gridSize)
        );
    }

    public function openMap(
        string $tableId,
        int $viewerUserId,
        string $sceneId
    ): TableScene {
        $this->assertDungeonMaster($tableId, $viewerUserId);
        $scene = $this->scenes->activate($tableId, $sceneId);
        $this->thresholds->welcomeParty($tableId, $scene);
        return $scene;
    }


    public function placeThreshold(
        string $tableId,
        int $viewerUserId,
        string $sceneId,
        string $type,
        float $x,
        float $y
    ): ThresholdMarker {
        return $this->thresholds->place($tableId, $viewerUserId, $sceneId, $type, $x, $y);
    }

    public function moveThreshold(
        string $tableId,
        int $viewerUserId,
        string $sceneId,
        string $markerId,
        float $x,
        float $y
    ): ThresholdMarker {
        return $this->thresholds->move($tableId, $viewerUserId, $sceneId, $markerId, $x, $y);
    }

    public function removeThreshold(
        string $tableId,
        int $viewerUserId,
        string $sceneId,
        string $markerId
    ): void {
        $this->thresholds->remove($tableId, $viewerUserId, $sceneId, $markerId);
    }

    public function deleteMap(string $tableId, int $viewerUserId, string $sceneId): string
    {
        $this->assertDungeonMaster($tableId, $viewerUserId);
        $sceneId = trim($sceneId);
        $scene = null;
        $scenes = $this->scenes->scenes($tableId);
        foreach ($scenes as $candidate) {
            if ($candidate->id() === $sceneId) {
                $scene = $candidate;
                break;
            }
        }
        if ($scene === null) {
            throw new AtlasDenied('That Scene could not be found in the Keeper\'s Atlas.');
        }
        if (count($scenes) <= 1) {
            throw new AtlasDenied('The Keeper cannot remove the final Scene from the Table.');
        }
        if ($scene->isActive()) {
            throw new AtlasDenied('The live Scene cannot be removed. Open another Scene first.');
        }

        $name = $scene->name();
        $this->cleaner->clear($tableId, $sceneId);
        return $name;
    }

    private function assertDungeonMaster(string $tableId, int $viewerUserId): void
    {
        $member = $this->members->find($tableId, $viewerUserId);

        if (
            $member === null
            || $member->status() !== TableMemberStatus::ACTIVE
            || $member->role() !== TableMemberRole::DUNGEON_MASTER
        ) {
            throw new AtlasDenied('Only the Dungeon Master may open the Keeper\'s Atlas.');
        }
    }
}
