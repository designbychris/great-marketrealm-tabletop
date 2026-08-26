<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tables\Services;

use DateTimeImmutable;
use GreatMarketrealmTabletop\Tables\Contracts\TableCapacityPolicy;
use GreatMarketrealmTabletop\Tables\Contracts\TableClock;
use GreatMarketrealmTabletop\Tables\Contracts\TableIdGenerator;
use GreatMarketrealmTabletop\Tables\Contracts\TableRepository;
use GreatMarketrealmTabletop\Tables\Models\Table;

final class InMemoryTableRepository implements TableRepository
{
    /** @var array<string,Table> */
    private array $tables = [];

    /** @return array<int,Table> */
    public function all(): array
    {
        return array_values($this->tables);
    }

    public function find(string $id): ?Table
    {
        return $this->tables[$id] ?? null;
    }

    public function save(Table $table): void
    {
        $this->tables[$table->id()] = $table;
    }

    public function activeCount(): int
    {
        return count(
            array_filter(
                $this->tables,
                static fn (Table $table): bool => $table->isActive()
            )
        );
    }
}

final class FixedCapacityPolicy implements TableCapacityPolicy
{
    public function __construct(private int $limit) {}

    public function limit(): int
    {
        return $this->limit;
    }
}

final class FixedClock implements TableClock
{
    public function __construct(private DateTimeImmutable $now) {}

    public function now(): DateTimeImmutable
    {
        return $this->now;
    }

    public function set(DateTimeImmutable $now): void
    {
        $this->now = $now;
    }
}

final class SequenceIdGenerator implements TableIdGenerator
{
    private int $next = 1;

    public function generate(): string
    {
        return 'table-' . $this->next++;
    }
}
