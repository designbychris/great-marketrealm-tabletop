<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tables\Models;

use GreatMarketrealmTabletop\Tables\Models\TableStatus;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class TableStatusTest extends TestCase
{
    public function testLifecycleContainsThreeInitialStates(): void
    {
        self::assertSame(
            ['preparing', 'active', 'ended'],
            TableStatus::all()
        );
    }

    public function testKnownStatusIsAccepted(): void
    {
        self::assertSame(
            TableStatus::ACTIVE,
            TableStatus::assert(TableStatus::ACTIVE)
        );
    }

    public function testUnknownStatusIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        TableStatus::assert('teleported-into-cheese');
    }
}
