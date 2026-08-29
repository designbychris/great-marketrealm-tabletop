<?php
declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Cartography\Services;

use GreatMarketrealmTabletop\Tables\Memberships\Contracts\TableMembershipRepository;
use GreatMarketrealmTabletop\Tables\Memberships\Models\TableMemberRole;
use GreatMarketrealmTabletop\Tables\Memberships\Models\TableMemberStatus;
use GreatMarketrealmTabletop\Tables\Scenes\Contracts\TableSceneRepository;
use GreatMarketrealmTabletop\Tabletop\Cartography\Exceptions\CartographyDenied;
use GreatMarketrealmTabletop\Tabletop\Cartography\Models\BattlemapImage;

defined('ABSPATH') || exit;

final class CartographersTable
{
    public function __construct(
        private TableMembershipRepository $members,
        private TableSceneRepository $scenes,
        private BattlemapInspector $images
    ) {}

    public function replaceActiveBattlemap(
        string $tableId,
        int $viewerUserId,
        int $attachmentId,
        string $sceneId = ''
    ): BattlemapImage {
        $member = $this->members->find(
            $tableId,
            $viewerUserId
        );

        if (
            $member === null
            || $member->status() !== TableMemberStatus::ACTIVE
            || $member->role() !== TableMemberRole::DUNGEON_MASTER
        ) {
            throw new CartographyDenied(
                'Only the Dungeon Master may change the active battlemap.'
            );
        }

        $scene = $this->targetScene($tableId, $sceneId);

        if ($scene === null) {
            throw new CartographyDenied(
                'Open a Scene before choosing its battlemap.'
            );
        }

        $image = $this->images->inspect($attachmentId);

        $scene->replaceMap(
            $image->attachmentId(),
            $image->width(),
            $image->height()
        );

        $this->scenes->save($scene);

        return $image;
    }
    /** @return array<string,mixed> */
    public function calibrateActiveGrid(
        string $tableId,
        int $viewerUserId,
        int $size,
        int $offsetX,
        int $offsetY,
        int $opacity,
        bool $visible,
        int $referenceWidth,
        string $sceneId = ''
    ): array {
        $member = $this->members->find($tableId, $viewerUserId);

        if (
            $member === null
            || $member->status() !== TableMemberStatus::ACTIVE
            || $member->role() !== TableMemberRole::DUNGEON_MASTER
        ) {
            throw new CartographyDenied(
                'Only the Dungeon Master may calibrate the active grid.'
            );
        }

        $scene = $this->targetScene($tableId, $sceneId);

        if ($scene === null) {
            throw new CartographyDenied(
                'Open a Scene before calibrating its grid.'
            );
        }

        $scene->calibrateGrid(
            $size,
            $offsetX,
            $offsetY,
            $opacity,
            $visible,
            $referenceWidth
        );
        $this->scenes->save($scene);

        return [
            'size' => $scene->gridSize(),
            'offset_x' => $scene->gridOffsetX(),
            'offset_y' => $scene->gridOffsetY(),
            'opacity' => $scene->gridOpacity(),
            'visible' => $scene->gridVisible(),
            'reference_width' => $scene->gridReferenceWidth(),
        ];
    }


    private function targetScene(string $tableId, string $sceneId): ?\GreatMarketrealmTabletop\Tables\Scenes\Models\TableScene
    {
        $sceneId = trim($sceneId);
        if ($sceneId !== '') {
            return $this->scenes->find($tableId, $sceneId);
        }

        foreach ($this->scenes->forTable($tableId) as $candidate) {
            if ($candidate->isActive()) {
                return $candidate;
            }
        }

        return null;
    }

}
