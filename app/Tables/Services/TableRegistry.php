<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tables\Services;

use GreatMarketrealmTabletop\Tables\Contracts\TableCapacityPolicy;
use GreatMarketrealmTabletop\Tables\Contracts\TableClock;
use GreatMarketrealmTabletop\Tables\Contracts\TableIdGenerator;
use GreatMarketrealmTabletop\Tables\Contracts\TableRepository;
use GreatMarketrealmTabletop\Tables\Contracts\TableStewardOverride;
use GreatMarketrealmTabletop\Tables\Exceptions\TableCapacityExceeded;
use GreatMarketrealmTabletop\Tables\Models\Table;
use GreatMarketrealmTabletop\Tables\Memberships\Services\TableGathering;
use GreatMarketrealmTabletop\Tables\Policies\WordPressTableLeasePolicy;
use GreatMarketrealmTabletop\Tables\Policies\WordPressTableStewardOverride;
use RuntimeException;

defined('ABSPATH') || exit;

final class TableRegistry
{
    private TableLeaseManager $leases;
    private TableStewardOverride $override;

    private ?TableGathering $gathering;

    public function __construct(
        private TableRepository $tables,
        private TableCapacityPolicy $capacity,
        private TableClock $clock,
        private TableIdGenerator $ids,
        ?TableLeaseManager $leases = null,
        ?TableStewardOverride $override = null,
        ?TableGathering $gathering = null
    ) {
        $this->leases = $leases ?? new TableLeaseManager(
            $tables,
            new WordPressTableLeasePolicy(),
            $clock
        );
        $this->override = $override ?? new WordPressTableStewardOverride();
        $this->gathering = $gathering;
    }

    public function prepare(int $dungeonMasterUserId, string $name, string $description = ''): Table
    {
        $table = Table::prepare(
            $this->ids->generate(),
            $dungeonMasterUserId,
            $name,
            $this->clock->now(),
            $description
        );
        $this->tables->save($table);

        if ($this->gathering !== null) {
            $this->gathering->seatDungeonMaster($table);
        }

        return $table;
    }

    public function activate(string $id): Table
    {
        $table = $this->required($id);
        $this->leases->reclaimExpired();
        $limit = $this->capacity->limit();

        if (
            $this->tables->activeCount() >= $limit
            && ! $this->override->mayBypassCapacity($table->dungeonMasterUserId())
        ) {
            throw TableCapacityExceeded::forLimit($limit);
        }

        $now = $this->clock->now();
        $table->activate($now, $this->leases->leaseExpiryFrom($now));
        $this->tables->save($table);
        return $table;
    }

    public function heartbeat(string $id): Table
    {
        return $this->leases->heartbeat($id);
    }

    public function reclaimExpired(): int
    {
        return $this->leases->reclaimExpired();
    }

    public function end(string $id): Table
    {
        $table = $this->required($id);
        $table->end($this->clock->now());
        $this->tables->save($table);
        return $table;
    }

    /** @return array<int,Table> */
    public function all(): array { return $this->tables->all(); }
    public function find(string $id): ?Table { return $this->tables->find($id); }

    /** @return array<int,Table> */
    public function ownedBy(int $dungeonMasterUserId): array
    {
        return array_values(array_filter(
            $this->tables->all(),
            static fn (Table $table): bool => $table->dungeonMasterUserId() === $dungeonMasterUserId
                && $table->status() !== 'ended'
        ));
    }

    private function required(string $id): Table
    {
        $table = $this->tables->find($id);
        if ($table === null) {
            throw new RuntimeException('The requested Table could not be found.');
        }
        return $table;
    }
}
