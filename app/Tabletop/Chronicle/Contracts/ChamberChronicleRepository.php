<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Chronicle\Contracts;

use GreatMarketrealmTabletop\Tabletop\Chronicle\Models\ChamberChronicleEvent;

defined('ABSPATH') || exit;

interface ChamberChronicleRepository
{
    /** @return array<int,ChamberChronicleEvent> */
    public function forTable(string $tableId): array;

    /** @return array<int,ChamberChronicleEvent> */
    public function forSession(string $tableId, string $sessionId): array;

    public function append(ChamberChronicleEvent $event): void;
}
