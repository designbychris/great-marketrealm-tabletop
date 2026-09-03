<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Sessions\Repositories;

use GreatMarketrealmTabletop\Tabletop\Sessions\Contracts\TableSessionRepository;
use GreatMarketrealmTabletop\Tabletop\Sessions\Models\TableSession;

defined('ABSPATH') || exit;

final class WordPressTableSessionRepository implements TableSessionRepository
{
    private const OPTION = 'gmrt_table_sessions';

    public function forTable(string $tableId): array
    {
        $records = $this->records();
        $tableRecords = is_array($records[$tableId] ?? null) ? $records[$tableId] : [];
        $sessions = [];

        foreach ($tableRecords as $record) {
            if (is_array($record)) {
                $sessions[] = TableSession::reconstitute($record);
            }
        }

        usort($sessions, static fn (TableSession $a, TableSession $b): int => $a->number() <=> $b->number());
        return $sessions;
    }

    public function currentForTable(string $tableId): ?TableSession
    {
        foreach (array_reverse($this->forTable($tableId)) as $session) {
            if ($session->isActive()) {
                return $session;
            }
        }

        return null;
    }

    public function save(TableSession $session): void
    {
        $records = $this->records();
        $tableId = $session->tableId();
        $tableRecords = is_array($records[$tableId] ?? null) ? $records[$tableId] : [];
        $tableRecords[$session->id()] = $session->toArray();
        $records[$tableId] = $tableRecords;
        update_option(self::OPTION, $records, false);
    }

    /** @return array<string,mixed> */
    private function records(): array
    {
        $records = get_option(self::OPTION, []);
        return is_array($records) ? $records : [];
    }
}
