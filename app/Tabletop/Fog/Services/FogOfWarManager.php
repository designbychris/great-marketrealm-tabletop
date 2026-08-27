<?php
declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Fog\Services;

use GreatMarketrealmTabletop\Tabletop\Fog\Contracts\FogOfWarRepository;
use GreatMarketrealmTabletop\Tables\Memberships\Contracts\TableMembershipRepository;
use GreatMarketrealmTabletop\Tables\Memberships\Models\TableMemberRole;
use GreatMarketrealmTabletop\Tables\Memberships\Models\TableMemberStatus;
use GreatMarketrealmTabletop\Tables\Scenes\Contracts\TableSceneRepository;
use GreatMarketrealmTabletop\Tables\Tokens\Contracts\TableTokenRepository;
use GreatMarketrealmTabletop\Tables\Tokens\Models\TableToken;
use GreatMarketrealmTabletop\Tables\Tokens\Models\TableTokenType;
use RuntimeException;

defined('ABSPATH') || exit;

final class FogOfWarManager
{
    public function __construct(
        private FogOfWarRepository $fog,
        private TableMembershipRepository $members,
        private TableSceneRepository $scenes,
        private TableTokenRepository $tokens,
        private FogCellMapper $mapper
    ) {}

    /** @return array<string,mixed> */
    public function configure(
        string $tableId,
        int $viewerUserId,
        bool $enabled,
        bool $clear = false
    ): array {
        $member = $this->members->find(
            $tableId,
            $viewerUserId
        );

        if (
            $member === null
            || $member->status() !== TableMemberStatus::ACTIVE
            || $member->role() !== TableMemberRole::DUNGEON_MASTER
        ) {
            throw new RuntimeException(
                'Only the Dungeon Master may change Fog of War.'
            );
        }

        $scene = $this->activeScene($tableId);
        $state = $this->fog->forScene(
            $tableId,
            $scene->id()
        );

        if ($clear) {
            $state->clear();
        }

        if ($enabled) {
            $state->enable();

            foreach (
                $this->tokens->forScene(
                    $tableId,
                    $scene->id()
                )
                as $token
            ) {
                if ($token->type() === TableTokenType::CHARACTER) {
                    $state->reveal(
                        $this->mapper->visibleAround(
                            $scene,
                            $token
                        )
                    );
                }
            }
        } else {
            $state->disable();
        }

        $this->fog->save($tableId, $state);

        return $state->toArray();
    }

    public function revealForMovement(
        string $tableId,
        TableToken $token
    ): void {
        if ($token->type() !== TableTokenType::CHARACTER) {
            return;
        }

        $scene = $this->scenes->find(
            $tableId,
            $token->sceneId()
        );

        if ($scene === null) {
            return;
        }

        $state = $this->fog->forScene(
            $tableId,
            $scene->id()
        );

        if (! $state->enabled()) {
            return;
        }

        $state->reveal(
            $this->mapper->visibleAround(
                $scene,
                $token
            )
        );

        $this->fog->save(
            $tableId,
            $state
        );
    }

    private function activeScene(
        string $tableId
    ): \GreatMarketrealmTabletop\Tables\Scenes\Models\TableScene {
        foreach ($this->scenes->forTable($tableId) as $scene) {
            if ($scene->isActive()) {
                return $scene;
            }
        }

        throw new RuntimeException(
            'Open a Scene before changing Fog of War.'
        );
    }
}
