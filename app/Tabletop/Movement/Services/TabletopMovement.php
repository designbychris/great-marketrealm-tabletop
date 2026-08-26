<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Movement\Services;

use GreatMarketrealmTabletop\Tabletop\Movement\Exceptions\StaleTokenRevision;
use GreatMarketrealmTabletop\Tabletop\Movement\Exceptions\TabletopMovementDenied;
use GreatMarketrealmTabletop\Tables\Contracts\TableRepository;
use GreatMarketrealmTabletop\Tables\Memberships\Contracts\TableMembershipRepository;
use GreatMarketrealmTabletop\Tables\Models\TableStatus;
use GreatMarketrealmTabletop\Tables\Scenes\Contracts\TableSceneRepository;
use GreatMarketrealmTabletop\Tables\Tokens\Contracts\TableTokenRepository;
use GreatMarketrealmTabletop\Tables\Tokens\Models\TableToken;
use RuntimeException;

defined('ABSPATH') || exit;

final class TabletopMovement
{
    public function __construct(
        private TableRepository $tables,
        private TableMembershipRepository $members,
        private TableSceneRepository $scenes,
        private TableTokenRepository $tokens,
        private TabletopMovementPolicy $policy
    ) {}

    public function move(
        string $tableId,
        int $viewerUserId,
        string $tokenId,
        float $x,
        float $y,
        int $expectedRevision
    ): TableToken {
        $table = $this->tables->find($tableId);

        if ($table === null) {
            throw new RuntimeException(
                'The requested Table could not be found.'
            );
        }

        if ($table->status() === TableStatus::ENDED) {
            throw new TabletopMovementDenied(
                'Tokens cannot move after the Table has ended.'
            );
        }

        $member = $this->members->find(
            $tableId,
            $viewerUserId
        );

        if ($member === null) {
            throw new TabletopMovementDenied(
                'Only active Table members may move tokens.'
            );
        }

        $token = $this->tokens->find(
            $tableId,
            $tokenId
        );

        if ($token === null) {
            throw new RuntimeException(
                'The requested Table token could not be found.'
            );
        }

        $scene = $this->scenes->find(
            $tableId,
            $token->sceneId()
        );

        if ($scene === null) {
            throw new RuntimeException(
                'The token Scene could not be found.'
            );
        }

        if (! $scene->isActive()) {
            throw new TabletopMovementDenied(
                'Only tokens on the active Scene may move.'
            );
        }

        if (! $this->policy->mayMove(
            $member,
            $token
        )) {
            throw new TabletopMovementDenied(
                'This Table member does not control that token.'
            );
        }

        if ($token->revision() !== $expectedRevision) {
            throw new StaleTokenRevision(
                'The token changed before this movement request was applied.'
            );
        }

        $scene->coordinates($x, $y);
        $token->move($x, $y);
        $this->tokens->save($token);

        return $token;
    }
}
