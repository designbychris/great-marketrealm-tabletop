<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Battle\Services;

use GreatMarketrealmTabletop\Tabletop\Battle\Contracts\D20Roller;
use GreatMarketrealmTabletop\Tabletop\Battle\Models\AttackRollMode;
use GreatMarketrealmTabletop\Tabletop\Battle\Models\CombatProfile;
use GreatMarketrealmTabletop\Tabletop\Battle\Services\AttackResolver;
use PHPUnit\Framework\TestCase;

final class AttackResolverAdvantageTest extends TestCase
{
    public function testAdvantageKeepsHigherD20(): void
    {
        $roller = $this->roller([4, 17]);

        $outcome = (new AttackResolver($roller))->resolve(
            new CombatProfile('a', 10, 3),
            new CombatProfile('b', 14, 0),
            AttackRollMode::ADVANTAGE
        );

        self::assertSame(17, $outcome->toArray()['roll']);
        self::assertSame([4, 17], $outcome->toArray()['rolls']);
        self::assertSame('advantage', $outcome->toArray()['roll_mode']);
    }

    public function testDisadvantageKeepsLowerD20(): void
    {
        $roller = $this->roller([18, 6]);

        $outcome = (new AttackResolver($roller))->resolve(
            new CombatProfile('a', 10, 3),
            new CombatProfile('b', 14, 0),
            AttackRollMode::DISADVANTAGE
        );

        self::assertSame(6, $outcome->toArray()['roll']);
        self::assertSame([18, 6], $outcome->toArray()['rolls']);
    }

    public function testNormalRollStillUsesOneD20(): void
    {
        $roller = $this->roller([12, 20]);

        $outcome = (new AttackResolver($roller))->resolve(
            new CombatProfile('a', 10, 3),
            new CombatProfile('b', 14, 0)
        );

        self::assertSame([12], $outcome->toArray()['rolls']);
        self::assertSame('normal', $outcome->toArray()['roll_mode']);
    }

    /** @param array<int,int> $rolls */
    private function roller(array $rolls): D20Roller
    {
        return new class($rolls) implements D20Roller {
            /** @param array<int,int> $rolls */
            public function __construct(
                private array $rolls
            ) {}

            public function roll(): int
            {
                return array_shift($this->rolls) ?? 1;
            }
        };
    }
}
