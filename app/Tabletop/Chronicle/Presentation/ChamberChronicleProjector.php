<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Chronicle\Presentation;

use GreatMarketrealmTabletop\Tabletop\Chronicle\Models\ChamberChronicleEvent;

defined('ABSPATH') || exit;

final class ChamberChronicleProjector
{
    private const MAX_ENTRIES = 12;

    /** @param array<int,ChamberChronicleEvent> $events @return array<int,array<string,mixed>> */
    public function project(array $events): array
    {
        $entries = array_map(static function (ChamberChronicleEvent $event): array {
            $record = $event->toArray();
            return [
                'id' => (string) ($record['id'] ?? ''),
                'kind' => (string) ($record['kind'] ?? 'satchel'),
                'action' => (string) ($record['action'] ?? 'roll'),
                'user_id' => (int) ($record['user_id'] ?? 0),
                'summary' => (string) ($record['summary'] ?? ''),
                'occurred_at' => (string) ($record['occurred_at'] ?? ''),
            ];
        }, $events);

        return array_reverse(array_slice($entries, -self::MAX_ENTRIES));
    }
}
