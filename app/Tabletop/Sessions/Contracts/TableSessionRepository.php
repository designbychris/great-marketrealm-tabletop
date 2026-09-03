<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Sessions\Contracts;

use GreatMarketrealmTabletop\Tabletop\Sessions\Models\TableSession;

defined('ABSPATH') || exit;

interface TableSessionRepository
{
    /** @return array<int,TableSession> */
    public function forTable(string $tableId): array;

    public function currentForTable(string $tableId): ?TableSession;

    public function save(TableSession $session): void;
}
