<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Battle\Models;

use GreatMarketrealmTabletop\Tabletop\Battle\Models\AttackOutcome;
use PHPUnit\Framework\TestCase;

final class AttackOutcomeTest extends TestCase
{
    public function testNaturalTwentyIsCriticalRegardlessOfArmorClass(): void
    {
        $outcome = new AttackOutcome(20, -5, 50);

        self::assertSame(
            AttackOutcome::CRITICAL_HIT,
            $outcome->result()
        );
        self::assertTrue($outcome->isHit());
    }

    public function testNaturalOneIsCriticalMissRegardlessOfModifier(): void
    {
        $outcome = new AttackOutcome(1, 30, 2);

        self::assertSame(
            AttackOutcome::CRITICAL_MISS,
            $outcome->result()
        );
        self::assertFalse($outcome->isHit());
    }

    public function testModifiedRollMeetingArmorClassHits(): void
    {
        $outcome = new AttackOutcome(12, 4, 16);

        self::assertSame(AttackOutcome::HIT, $outcome->result());
        self::assertSame(16, $outcome->total());
    }

    public function testModifiedRollBelowArmorClassMisses(): void
    {
        $outcome = new AttackOutcome(11, 4, 16);

        self::assertSame(AttackOutcome::MISS, $outcome->result());
    }
}
