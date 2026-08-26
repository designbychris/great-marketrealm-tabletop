<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Battle\Contracts;

use GreatMarketrealmTabletop\Tabletop\Battle\Models\BattleEvent;

defined('ABSPATH') || exit;

interface BattleEventRepository
{
    /** @return array<int,BattleEvent> */
    public function forEncounter(
        string $tableId,
        string $encounterId
    ): array;

    public function append(BattleEvent $event): void;
}
