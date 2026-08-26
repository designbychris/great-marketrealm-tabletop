<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tables\Services;

use GreatMarketrealmTabletop\Tables\Contracts\TableCapacityPolicy;
use GreatMarketrealmTabletop\Tables\Contracts\TableClock;
use GreatMarketrealmTabletop\Tables\Contracts\TableIdGenerator;
use GreatMarketrealmTabletop\Tables\Contracts\TableRepository;
use GreatMarketrealmTabletop\Tables\Exceptions\TableCapacityExceeded;
use GreatMarketrealmTabletop\Tables\Models\Table;
use RuntimeException;

defined('ABSPATH') || exit;

final class TableRegistry
{
    public function __construct(
        private TableRepository $tables,
        private TableCapacityPolicy $capacity,
        private TableClock $clock,
        private TableIdGenerator $ids
    ) {}

    public function prepare(
        int $dungeonMasterUserId,
        string $name
    ): Table {
        $table = Table::prepare(
            $this->ids->generate(),
            $dungeonMasterUserId,
            $name,
            $this->clock->now()
        );

        $this->tables->save($table);

        return $table;
    }

    public function activate(string $id): Table
    {
        $table = $this->required($id);

        if (! $table->isActive()) {
            $limit = $this->capacity->limit();

            if ($this->tables->activeCount() >= $limit) {
                throw TableCapacityExceeded::forLimit($limit);
            }
        }

        $table->activate($this->clock->now());
        $this->tables->save($table);

        return $table;
    }

    public function end(string $id): Table
    {
        $table = $this->required($id);
        $table->end($this->clock->now());
        $this->tables->save($table);

        return $table;
    }

    /** @return array<int,Table> */
    public function all(): array
    {
        return $this->tables->all();
    }

    public function find(string $id): ?Table
    {
        return $this->tables->find($id);
    }

    private function required(string $id): Table
    {
        $table = $this->tables->find($id);

        if ($table === null) {
            throw new RuntimeException(
                'The requested Table could not be found.'
            );
        }

        return $table;
    }
}
