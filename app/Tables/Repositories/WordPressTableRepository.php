<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tables\Repositories;

use GreatMarketrealmTabletop\Tables\Contracts\TableRepository;
use GreatMarketrealmTabletop\Tables\Models\Table;
use GreatMarketrealmTabletop\Tables\Models\TableStatus;

defined('ABSPATH') || exit;

/**
 * Early persistent registry adapter.
 *
 * Table metadata is intentionally isolated behind TableRepository so later
 * phases can migrate storage without changing gameplay/domain services.
 */
final class WordPressTableRepository implements TableRepository
{
    private const OPTION = 'gmrt_tables';

    /** @return array<int,Table> */
    public function all(): array
    {
        $tables = [];

        foreach ($this->records() as $record) {
            if (! is_array($record)) {
                continue;
            }

            $tables[] = Table::reconstitute($record);
        }

        return $tables;
    }

    public function find(string $id): ?Table
    {
        $records = $this->records();
        $record = $records[$id] ?? null;

        if (is_array($record)) {
            return Table::reconstitute($record);
        }

        // Compatibility for Tables created by early development builds where
        // the option storage key did not necessarily match the persisted UUID.
        // all() can still surface those Tables in Pippin's Atlas, so find() must
        // resolve the same record by its authoritative embedded ID.
        foreach ($records as $candidate) {
            if (
                is_array($candidate)
                && (string) ($candidate['id'] ?? '') === $id
            ) {
                return Table::reconstitute($candidate);
            }
        }

        return null;
    }

    public function save(Table $table): void
    {
        $records = $this->records();
        $records[$table->id()] = $table->toArray();

        update_option(
            self::OPTION,
            $records,
            false
        );
    }

    public function activeCount(): int
    {
        $count = 0;

        foreach ($this->records() as $record) {
            if (
                is_array($record)
                && ($record['status'] ?? '') === TableStatus::ACTIVE
            ) {
                ++$count;
            }
        }

        return $count;
    }

    /** @return array<string,array<string,mixed>> */
    private function records(): array
    {
        $records = get_option(self::OPTION, []);

        return is_array($records)
            ? $records
            : [];
    }
}
