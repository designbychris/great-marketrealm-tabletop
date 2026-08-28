<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Chronicle\Repositories;

use GreatMarketrealmTabletop\Tabletop\Chronicle\Contracts\ChamberChronicleRepository;
use GreatMarketrealmTabletop\Tabletop\Chronicle\Models\ChamberChronicleEvent;

defined('ABSPATH') || exit;

final class WordPressChamberChronicleRepository implements ChamberChronicleRepository
{
    private const OPTION = 'gmrt_chamber_chronicle';

    public function forTable(string $tableId): array
    {
        $events = [];
        foreach ($this->records()[$tableId] ?? [] as $record) {
            if (is_array($record)) {
                $events[] = ChamberChronicleEvent::reconstitute($record);
            }
        }
        return $events;
    }

    public function append(ChamberChronicleEvent $event): void
    {
        $record = $event->toArray();
        $records = $this->records();
        $records[(string) $record['table_id']][] = $record;
        update_option(self::OPTION, $records, false);
    }

    /** @return array<string,mixed> */
    private function records(): array
    {
        $records = get_option(self::OPTION, []);
        return is_array($records) ? $records : [];
    }
}
