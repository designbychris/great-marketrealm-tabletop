<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tables\Services;

use DateInterval;
use DateTimeImmutable;
use GreatMarketrealmTabletop\Tables\Contracts\TableClock;
use GreatMarketrealmTabletop\Tables\Contracts\TableLeasePolicy;
use GreatMarketrealmTabletop\Tables\Contracts\TableRepository;
use GreatMarketrealmTabletop\Tables\Exceptions\TableLeaseExpired;
use GreatMarketrealmTabletop\Tables\Models\Table;
use RuntimeException;

defined('ABSPATH') || exit;

final class TableLeaseManager
{
    public function __construct(
        private TableRepository $tables,
        private TableLeasePolicy $policy,
        private TableClock $clock
    ) {}

    public function heartbeat(string $id): Table
    {
        $table = $this->required($id);
        $now = $this->clock->now();

        if ($table->leaseExpired($now)) {
            $table->expire($now);
            $this->tables->save($table);
            throw new TableLeaseExpired('The Table lease expired before the heartbeat arrived.');
        }

        $table->heartbeat($now, $this->leaseExpiryFrom($now));
        $this->tables->save($table);
        return $table;
    }

    public function reclaimExpired(): int
    {
        $now = $this->clock->now();
        $reclaimed = 0;

        foreach ($this->tables->all() as $table) {
            if (! $table->leaseExpired($now)) {
                continue;
            }
            $table->expire($now);
            $this->tables->save($table);
            ++$reclaimed;
        }

        return $reclaimed;
    }

    public function leaseExpiryFrom(DateTimeImmutable $from): DateTimeImmutable
    {
        $seconds = $this->policy->leaseSeconds()
            + $this->policy->heartbeatGraceSeconds();

        return $from->add(new DateInterval('PT' . $seconds . 'S'));
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
