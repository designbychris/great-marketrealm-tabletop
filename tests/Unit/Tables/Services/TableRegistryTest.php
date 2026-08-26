<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tables\Services;

require_once __DIR__ . '/TableTestDoubles.php';

use DateTimeImmutable;
use GreatMarketrealmTabletop\Tables\Exceptions\TableCapacityExceeded;
use GreatMarketrealmTabletop\Tables\Models\TableStatus;
use GreatMarketrealmTabletop\Tables\Services\TableRegistry;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class TableRegistryTest extends TestCase
{
    private InMemoryTableRepository $repository;

    private FixedClock $clock;

    private TableRegistry $registry;

    protected function setUp(): void
    {
        $this->repository = new InMemoryTableRepository();
        $this->clock = new FixedClock(
            new DateTimeImmutable('2026-08-25T18:00:00+01:00')
        );
        $this->registry = new TableRegistry(
            $this->repository,
            new FixedCapacityPolicy(2),
            $this->clock,
            new SequenceIdGenerator()
        );
    }

    public function testPreparingATableAssignsStableIdentityAndOwner(): void
    {
        $table = $this->registry->prepare(
            42,
            'Tuesday at the Giggling Gourd'
        );

        self::assertSame('table-1', $table->id());
        self::assertSame(42, $table->dungeonMasterUserId());
        self::assertSame(TableStatus::PREPARING, $table->status());
        self::assertNotNull($this->registry->find('table-1'));
    }

    public function testTwoTablesMayBeActiveAtTheSameTime(): void
    {
        $one = $this->registry->prepare(42, 'Table One');
        $two = $this->registry->prepare(84, 'Table Two');

        $this->registry->activate($one->id());
        $this->registry->activate($two->id());

        self::assertSame(2, $this->repository->activeCount());
    }

    public function testThirdSimultaneouslyActiveTableIsRejected(): void
    {
        $one = $this->registry->prepare(42, 'Table One');
        $two = $this->registry->prepare(84, 'Table Two');
        $three = $this->registry->prepare(126, 'Table Three');

        $this->registry->activate($one->id());
        $this->registry->activate($two->id());

        $this->expectException(TableCapacityExceeded::class);
        $this->expectExceptionMessage(
            '2 simultaneously active tables'
        );

        $this->registry->activate($three->id());
    }

    public function testEndingATableImmediatelyReleasesCapacity(): void
    {
        $one = $this->registry->prepare(42, 'Table One');
        $two = $this->registry->prepare(84, 'Table Two');
        $three = $this->registry->prepare(126, 'Table Three');

        $this->registry->activate($one->id());
        $this->registry->activate($two->id());
        $this->registry->end($one->id());
        $this->registry->activate($three->id());

        self::assertSame(2, $this->repository->activeCount());
        self::assertSame(TableStatus::ENDED, $one->status());
        self::assertSame(TableStatus::ACTIVE, $three->status());
    }

    public function testMissingTableCannotBeActivated(): void
    {
        $this->expectException(RuntimeException::class);

        $this->registry->activate('missing-table');
    }

    public function testRegistryListsPreparedTables(): void
    {
        $this->registry->prepare(42, 'One');
        $this->registry->prepare(42, 'Two');

        self::assertCount(2, $this->registry->all());
    }
}
