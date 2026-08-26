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
        $record = $this->records()[$id] ?? null;

        return is_array($record)
            ? Table::reconstitute($record)
            : null;
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
