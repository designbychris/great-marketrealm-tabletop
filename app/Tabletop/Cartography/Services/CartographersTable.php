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
        int $attachmentId
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

        $scene = null;
        foreach ($this->scenes->forTable($tableId) as $candidate) {
            if ($candidate->isActive()) {
                $scene = $candidate;
                break;
            }
        }

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
        bool $visible
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

        $scene = null;
        foreach ($this->scenes->forTable($tableId) as $candidate) {
            if ($candidate->isActive()) {
                $scene = $candidate;
                break;
            }
        }

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
            $visible
        );
        $this->scenes->save($scene);

        return [
            'size' => $scene->gridSize(),
            'offset_x' => $scene->gridOffsetX(),
            'offset_y' => $scene->gridOffsetY(),
            'opacity' => $scene->gridOpacity(),
            'visible' => $scene->gridVisible(),
        ];
    }

}
