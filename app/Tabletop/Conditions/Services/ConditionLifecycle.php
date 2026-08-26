<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Conditions\Services;

use GreatMarketrealmTabletop\Tabletop\Battle\Contracts\BattleEventRepository;
use GreatMarketrealmTabletop\Tabletop\Battle\Models\BattleEvent;
use GreatMarketrealmTabletop\Tabletop\Conditions\Contracts\ConditionRepository;
use GreatMarketrealmTabletop\Tabletop\Encounters\Models\Encounter;
use GreatMarketrealmTabletop\Tables\Contracts\TableClock;

defined('ABSPATH') || exit;

final class ConditionLifecycle
{
    public function __construct(
        private ConditionRepository $conditions,
        private BattleEventRepository $events,
        private TableClock $clock
    ) {}

    public function turnEnded(
        string $tableId,
        Encounter $encounter,
        string $tokenId
    ): void {
        foreach (
            $this->conditions->forToken($tableId, $tokenId)
            as $condition
        ) {
            $next = $condition->afterTurn();

            if ($next !== null) {
                if (
                    $next->turnsRemaining()
                    !== $condition->turnsRemaining()
                ) {
                    $this->conditions->save(
                        $tableId,
                        $next
                    );
                }
                continue;
            }

            $this->conditions->remove(
                $tableId,
                $tokenId,
                $condition->condition()
            );

            $this->events->append(
                new BattleEvent(
                    bin2hex(random_bytes(12)),
                    $tableId,
                    (string) $encounter->toArray()['id'],
                    'condition-expired',
                    $tokenId,
                    $encounter->round(),
                    $encounter->turnIndex(),
                    $this->clock->now(),
                    ['condition' => $condition->condition()]
                )
            );
        }
    }
}
