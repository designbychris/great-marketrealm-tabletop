<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Battle\Models;

use GreatMarketrealmTabletop\Tabletop\Battle\Models\DeathSaveOutcome;
use PHPUnit\Framework\TestCase;

final class DeathSaveOutcomeTest extends TestCase
{
    public function testTenOrHigherIsSuccess(): void
    {
        $outcome = new DeathSaveOutcome(10);
        self::assertSame(DeathSaveOutcome::SUCCESS, $outcome->result());
        self::assertSame(1, $outcome->successes());
    }

    public function testNineOrLowerIsFailure(): void
    {
        $outcome = new DeathSaveOutcome(9);
        self::assertSame(DeathSaveOutcome::FAILURE, $outcome->result());
        self::assertSame(1, $outcome->failures());
    }

    public function testNaturalOneCountsAsTwoFailures(): void
    {
        $outcome = new DeathSaveOutcome(1);
        self::assertSame(DeathSaveOutcome::NATURAL_ONE, $outcome->result());
        self::assertSame(2, $outcome->failures());
    }

    public function testNaturalTwentyRevives(): void
    {
        $outcome = new DeathSaveOutcome(20);
        self::assertSame(DeathSaveOutcome::NATURAL_TWENTY, $outcome->result());
        self::assertTrue($outcome->revives());
    }
}
