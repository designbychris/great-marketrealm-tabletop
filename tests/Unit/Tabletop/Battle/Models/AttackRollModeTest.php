<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Battle\Models;

use GreatMarketrealmTabletop\Tabletop\Battle\Models\AttackRollMode;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class AttackRollModeTest extends TestCase
{
    public function testSupportedModesAreAccepted(): void
    {
        self::assertSame(
            'advantage',
            AttackRollMode::assert('advantage')
        );
        self::assertSame(
            'disadvantage',
            AttackRollMode::assert('disadvantage')
        );
    }

    public function testUnknownModeIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        AttackRollMode::assert('extra-lucky');
    }

    public function testFactorsCancelAdvantageAndDisadvantage(): void
    {
        self::assertSame(
            AttackRollMode::NORMAL,
            AttackRollMode::fromFactors(true, true)
        );
        self::assertSame(
            AttackRollMode::ADVANTAGE,
            AttackRollMode::fromFactors(true, false)
        );
        self::assertSame(
            AttackRollMode::DISADVANTAGE,
            AttackRollMode::fromFactors(false, true)
        );
    }

}
