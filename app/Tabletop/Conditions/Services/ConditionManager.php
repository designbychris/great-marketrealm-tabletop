<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Conditions\Services;

use GreatMarketrealmTabletop\Tabletop\Battle\Contracts\BattleEventRepository;
use GreatMarketrealmTabletop\Tabletop\Battle\Models\BattleEvent;
use GreatMarketrealmTabletop\Tabletop\Conditions\Contracts\ConditionRepository;
use GreatMarketrealmTabletop\Tabletop\Conditions\Exceptions\ConditionControlDenied;
use GreatMarketrealmTabletop\Tabletop\Conditions\Models\ConditionType;
use GreatMarketrealmTabletop\Tabletop\Conditions\Models\TokenCondition;
use GreatMarketrealmTabletop\Tabletop\Encounters\Contracts\EncounterRepository;
use GreatMarketrealmTabletop\Tables\Contracts\TableClock;
use GreatMarketrealmTabletop\Tables\Memberships\Contracts\TableMembershipRepository;
use GreatMarketrealmTabletop\Tables\Tokens\Contracts\TableTokenRepository;
use RuntimeException;

defined('ABSPATH') || exit;

final class ConditionManager
{
    public function __construct(
        private ConditionRepository $conditions,
        private EncounterRepository $encounters,
        private TableMembershipRepository $members,
        private TableTokenRepository $tokens,
        private BattleEventRepository $events,
        private TableClock $clock
    ) {}

    public function apply(
        string $tableId,
        int $viewerUserId,
        string $encounterId,
        string $tokenId,
        string $condition,
        ?int $turnsRemaining = null
    ): TokenCondition {
        $encounter = $this->requireControl(
            $tableId,
            $viewerUserId,
            $encounterId
        );
        $condition = ConditionType::assert($condition);

        if ($turnsRemaining !== null) {
            $turnsRemaining = max(1, min(99, $turnsRemaining));
        }

        $token = $this->tokens->find($tableId, $tokenId);
        if (
            $token === null
            || $token->sceneId() !== $encounter->sceneId()
        ) {
            throw new RuntimeException(
                'Conditions may only affect tokens on the Encounter Scene.'
            );
        }

        $applied = new TokenCondition(
            $tokenId,
            $condition,
            $turnsRemaining,
            $this->clock->now()
        );

        $this->conditions->save($tableId, $applied);
        $this->record(
            $tableId,
            $encounter,
            $tokenId,
            'condition-applied',
            [
                'condition' => $condition,
                'turns_remaining' => $turnsRemaining,
            ]
        );

        return $applied;
    }

    public function remove(
        string $tableId,
        int $viewerUserId,
        string $encounterId,
        string $tokenId,
        string $condition
    ): void {
        $encounter = $this->requireControl(
            $tableId,
            $viewerUserId,
            $encounterId
        );
        $condition = ConditionType::assert($condition);

        $this->conditions->remove(
            $tableId,
            $tokenId,
            $condition
        );

        $this->record(
            $tableId,
            $encounter,
            $tokenId,
            'condition-removed',
            ['condition' => $condition]
        );
    }

    private function requireControl(
        string $tableId,
        int $viewerUserId,
        string $encounterId
    ): \GreatMarketrealmTabletop\Tabletop\Encounters\Models\Encounter {
        $member = $this->members->find(
            $tableId,
            $viewerUserId
        );

        if ($member === null || ! $member->isActive()
            || ! $member->isDungeonMaster()) {
            throw new ConditionControlDenied(
                'Only the active Dungeon Master may manage conditions.'
            );
        }

        $encounter = $this->encounters->find(
            $tableId,
            $encounterId
        );

        if ($encounter === null || $encounter->isEnded()) {
            throw new RuntimeException(
                'Conditions require a current Encounter.'
            );
        }

        return $encounter;
    }

    /** @param array<string,mixed> $payload */
    private function record(
        string $tableId,
        \GreatMarketrealmTabletop\Tabletop\Encounters\Models\Encounter $encounter,
        string $tokenId,
        string $type,
        array $payload
    ): void {
        $this->events->append(
            new BattleEvent(
                bin2hex(random_bytes(12)),
                $tableId,
                (string) $encounter->toArray()['id'],
                $type,
                $tokenId,
                $encounter->round(),
                $encounter->turnIndex(),
                $this->clock->now(),
                $payload
            )
        );
    }
}
