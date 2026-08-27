<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Battle\Services;

use GreatMarketrealmTabletop\Tabletop\Battle\Models\AttackRangeAssessment;
use GreatMarketrealmTabletop\Tabletop\Battle\Models\CombatProfile;
use GreatMarketrealmTabletop\Tabletop\Battle\Services\AttackRangeResolver;
use PHPUnit\Framework\TestCase;

final class AttackRangeResolverTest extends TestCase
{
    private AttackRangeResolver $resolver;

    protected function setUp(): void
    {
        $this->resolver = new AttackRangeResolver();
    }

    public function testMeleeAttackIsInRangeAtFiveFeet(): void
    {
        $assessment = $this->resolver->assess(
            5,
            new CombatProfile('a', 10, 0, 5, 5)
        );

        self::assertTrue($assessment->inRange());
        self::assertSame(
            AttackRangeAssessment::NORMAL,
            $assessment->status()
        );
    }

    public function testMeleeAttackIsOutOfRangeAtTenFeet(): void
    {
        $assessment = $this->resolver->assess(
            10,
            new CombatProfile('a', 10, 0, 5, 5)
        );

        self::assertFalse($assessment->inRange());
        self::assertSame(
            AttackRangeAssessment::OUT,
            $assessment->status()
        );
    }

    public function testRangedAttackRecognizesLongRange(): void
    {
        $assessment = $this->resolver->assess(
            45,
            new CombatProfile('a', 10, 0, 30, 60)
        );

        self::assertTrue($assessment->inRange());
        self::assertTrue($assessment->longRange());
        self::assertSame(
            AttackRangeAssessment::LONG,
            $assessment->status()
        );
    }

    public function testRangedAttackRejectsBeyondLongRange(): void
    {
        $assessment = $this->resolver->assess(
            65,
            new CombatProfile('a', 10, 0, 30, 60)
        );

        self::assertFalse($assessment->inRange());
    }
}
