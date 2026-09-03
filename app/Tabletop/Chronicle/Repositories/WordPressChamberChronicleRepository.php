<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Chronicle\Repositories;

use GreatMarketrealmTabletop\Tabletop\Chronicle\Contracts\ChamberChronicleRepository;
use GreatMarketrealmTabletop\Tabletop\Chronicle\Models\ChamberChronicleEvent;
use GreatMarketrealmTabletop\Tabletop\Sessions\Repositories\WordPressTableSessionRepository;

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

    /** @return array<int,ChamberChronicleEvent> */
    public function forSession(string $tableId, string $sessionId): array
    {
        return array_values(array_filter($this->forTable($tableId), static fn (ChamberChronicleEvent $event): bool => $event->sessionId() === $sessionId));
    }

    public function append(ChamberChronicleEvent $event): void
    {
        if ($event->sessionId() === '') {
            $record = $event->toArray();
            $session = (new WordPressTableSessionRepository())->currentForTable((string) ($record['table_id'] ?? ''));
            if ($session !== null) {
                $event = $event->withSessionId($session->id());
            }
        }
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
