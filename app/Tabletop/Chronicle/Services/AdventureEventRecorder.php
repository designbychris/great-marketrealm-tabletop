<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Chronicle\Services;

use GreatMarketrealmTabletop\Tabletop\Chronicle\Contracts\ChamberChronicleRepository;
use GreatMarketrealmTabletop\Tabletop\Chronicle\Models\ChamberChronicleEvent;
use GreatMarketrealmTabletop\Tables\Contracts\TableClock;

defined('ABSPATH') || exit;

/**
 * IV.37.2 — records objective Forge/adventure facts in the existing Chronicle.
 *
 * This is deliberately not a second history store. Adventure facts become
 * ordinary Chamber Chronicle events with a structured payload that later
 * Session recap phases can project without guessing from prose.
 */
final class AdventureEventRecorder
{
    public function __construct(
        private ChamberChronicleRepository $chronicle,
        private TableClock $clock
    ) {}

    /** @param array<string,mixed> $payload */
    public function record(
        string $tableId,
        int $userId,
        string $action,
        string $summary,
        array $payload = []
    ): void {
        $tableId = trim($tableId);
        $action = trim($action);
        $summary = trim($summary);

        if ($tableId === '' || $userId < 1 || $action === '' || $summary === '') {
            return;
        }

        $this->chronicle->append(new ChamberChronicleEvent(
            bin2hex(random_bytes(12)),
            $tableId,
            $userId,
            'keeper-adventure',
            'The Adventure',
            'adventure',
            $action,
            $summary,
            $this->clock->now(),
            ['adventure' => $payload]
        ));
    }
}
