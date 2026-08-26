<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Services;

use GreatMarketrealmTabletop\Tabletop\Exceptions\TabletopAccessDenied;
use GreatMarketrealmTabletop\Tabletop\Models\TabletopChamberState;
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
        private TableTokenRepository $tokens
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

        return new TabletopChamberState(
            $table->toArray(),
            $viewer->toArray(),
            $members,
            $activeScene?->toArray(),
            $tokens
        );
    }
}
