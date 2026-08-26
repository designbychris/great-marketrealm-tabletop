<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tables\Services;

require_once __DIR__ . '/TableTestDoubles.php';

use DateTimeImmutable;
use GreatMarketrealmTabletop\Tables\Models\TableStatus;
use PHPUnit\Framework\TestCase;

final class TableRegistryCapacityRulesTest extends TestCase
{
    public function testExpiredSlotIsReclaimedBeforeNewActivation(): void
    {
        $repo = new InMemoryTableRepository();
        $clock = new FixedClock(new DateTimeImmutable('2026-08-26T10:00:00+01:00'));
        $leases = new TableLeaseManager($repo, new FixedLeasePolicy(300, 60), $clock);
        $registry = new TableRegistry($repo, new FixedCapacityPolicy(1), $clock, new SequenceIdGenerator(), $leases, new FixedStewardOverride());
        $one = $registry->prepare(42, 'One');
        $two = $registry->prepare(84, 'Two');
        $registry->activate($one->id());
        $clock->set(new DateTimeImmutable('2026-08-26T10:07:00+01:00'));
        $registry->activate($two->id());
        self::assertSame(TableStatus::ENDED, $one->status());
        self::assertSame(TableStatus::ACTIVE, $two->status());
    }

    public function testTrustedStewardMayBypassCapacity(): void
    {
        $repo = new InMemoryTableRepository();
        $clock = new FixedClock(new DateTimeImmutable('2026-08-26T10:00:00+01:00'));
        $leases = new TableLeaseManager($repo, new FixedLeasePolicy(), $clock);
        $registry = new TableRegistry($repo, new FixedCapacityPolicy(1), $clock, new SequenceIdGenerator(), $leases, new FixedStewardOverride([84]));
        $one = $registry->prepare(42, 'One');
        $two = $registry->prepare(84, 'Steward Test');
        $registry->activate($one->id());
        $registry->activate($two->id());
        self::assertSame(2, $repo->activeCount());
    }
}
