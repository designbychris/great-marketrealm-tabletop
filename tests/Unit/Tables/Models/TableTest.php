<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tables\Models;

use DateTimeImmutable;
use GreatMarketrealmTabletop\Tables\Exceptions\InvalidTableTransition;
use GreatMarketrealmTabletop\Tables\Models\Table;
use GreatMarketrealmTabletop\Tables\Models\TableStatus;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class TableTest extends TestCase
{
    public function testNewTableBeginsPreparing(): void
    {
        $table = $this->table();

        self::assertSame('table-1', $table->id());
        self::assertSame(42, $table->dungeonMasterUserId());
        self::assertSame('The First Table', $table->name());
        self::assertSame(TableStatus::PREPARING, $table->status());
        self::assertNull($table->activatedAt());
        self::assertNull($table->endedAt());
    }

    public function testPreparingTableMayActivateAndThenEnd(): void
    {
        $table = $this->table();
        $activated = new DateTimeImmutable('2026-08-25T18:00:00+01:00');
        $ended = new DateTimeImmutable('2026-08-25T21:00:00+01:00');

        $table->activate($activated);
        self::assertSame(TableStatus::ACTIVE, $table->status());
        self::assertSame($activated, $table->activatedAt());

        $table->end($ended);
        self::assertSame(TableStatus::ENDED, $table->status());
        self::assertSame($ended, $table->endedAt());
    }

    public function testPreparingTableCannotEndBeforeActivation(): void
    {
        $this->expectException(InvalidTableTransition::class);

        $this->table()->end(new DateTimeImmutable());
    }

    public function testActiveTableCannotActivateTwice(): void
    {
        $table = $this->table();
        $table->activate(new DateTimeImmutable());

        $this->expectException(InvalidTableTransition::class);

        $table->activate(new DateTimeImmutable());
    }

    public function testTableRequiresDungeonMaster(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Table::prepare(
            'table-1',
            0,
            'No DM',
            new DateTimeImmutable()
        );
    }

    public function testTableRequiresName(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Table::prepare(
            'table-1',
            42,
            '   ',
            new DateTimeImmutable()
        );
    }

    public function testTableRoundTripsThroughPersistentRecord(): void
    {
        $table = $this->table();
        $table->activate(
            new DateTimeImmutable('2026-08-25T18:00:00+01:00')
        );

        $restored = Table::reconstitute($table->toArray());

        self::assertSame($table->toArray(), $restored->toArray());
    }

    private function table(): Table
    {
        return Table::prepare(
            'table-1',
            42,
            'The First Table',
            new DateTimeImmutable('2026-08-25T17:50:00+01:00')
        );
    }
}
