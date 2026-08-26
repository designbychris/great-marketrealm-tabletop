<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tables\Services;

require_once __DIR__ . '/TableTestDoubles.php';

use DateTimeImmutable;
use GreatMarketrealmTabletop\Tables\Exceptions\TableLeaseExpired;
use GreatMarketrealmTabletop\Tables\Models\Table;
use GreatMarketrealmTabletop\Tables\Models\TableStatus;
use PHPUnit\Framework\TestCase;

final class TableLeaseManagerTest extends TestCase
{
    public function testLeaseExpiryIncludesHeartbeatGrace(): void
    {
        $repo = new InMemoryTableRepository();
        $clock = new FixedClock(new DateTimeImmutable('2026-08-26T10:00:00+01:00'));
        $leases = new TableLeaseManager($repo, new FixedLeasePolicy(900, 120), $clock);
        self::assertSame('2026-08-26T10:17:00+01:00', $leases->leaseExpiryFrom($clock->now())->format(DATE_ATOM));
    }

    public function testHeartbeatRenewsActiveLease(): void
    {
        $repo = new InMemoryTableRepository();
        $clock = new FixedClock(new DateTimeImmutable('2026-08-26T10:00:00+01:00'));
        $leases = new TableLeaseManager($repo, new FixedLeasePolicy(), $clock);
        $table = Table::prepare('table-1', 42, 'Morning', $clock->now());
        $table->activate($clock->now(), $leases->leaseExpiryFrom($clock->now()));
        $repo->save($table);
        $clock->set(new DateTimeImmutable('2026-08-26T10:10:00+01:00'));
        $leases->heartbeat('table-1');
        self::assertSame('2026-08-26T10:27:00+01:00', $table->leaseExpiresAt()?->format(DATE_ATOM));
    }

    public function testExpiredTableIsReclaimed(): void
    {
        $repo = new InMemoryTableRepository();
        $clock = new FixedClock(new DateTimeImmutable('2026-08-26T10:00:00+01:00'));
        $leases = new TableLeaseManager($repo, new FixedLeasePolicy(300, 60), $clock);
        $table = Table::prepare('table-1', 42, 'Morning', $clock->now());
        $table->activate($clock->now(), $leases->leaseExpiryFrom($clock->now()));
        $repo->save($table);
        $clock->set(new DateTimeImmutable('2026-08-26T10:07:00+01:00'));
        self::assertSame(1, $leases->reclaimExpired());
        self::assertSame(TableStatus::ENDED, $table->status());
    }

    public function testLateHeartbeatEndsTableAndThrows(): void
    {
        $repo = new InMemoryTableRepository();
        $clock = new FixedClock(new DateTimeImmutable('2026-08-26T10:00:00+01:00'));
        $leases = new TableLeaseManager($repo, new FixedLeasePolicy(300, 60), $clock);
        $table = Table::prepare('table-1', 42, 'Morning', $clock->now());
        $table->activate($clock->now(), $leases->leaseExpiryFrom($clock->now()));
        $repo->save($table);
        $clock->set(new DateTimeImmutable('2026-08-26T10:07:00+01:00'));
        $this->expectException(TableLeaseExpired::class);
        $leases->heartbeat('table-1');
    }
}
