<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Battle\Services;

use GreatMarketrealmTabletop\Tabletop\Battle\Contracts\DamageDieRoller;
use GreatMarketrealmTabletop\Tabletop\Battle\Models\DamageProfile;
use GreatMarketrealmTabletop\Tabletop\Battle\Services\DamageResolver;
use PHPUnit\Framework\TestCase;

final class DamageResolverTest extends TestCase
{
    public function testNormalHitRollsConfiguredDiceOnce(): void
    {
        $roller = new class implements DamageDieRoller {
            public function roll(int $sides): int
            {
                return 4;
            }
        };

        $damage = (new DamageResolver($roller))->resolve(
            new DamageProfile('token-a', 2, 6, 3),
            false
        );

        self::assertSame([4, 4], $damage->rolls());
        self::assertSame(11, $damage->total());
        self::assertFalse($damage->critical());
    }

    public function testCriticalHitDoublesDamageDiceButNotModifier(): void
    {
        $roller = new class implements DamageDieRoller {
            public function roll(int $sides): int
            {
                return 3;
            }
        };

        $damage = (new DamageResolver($roller))->resolve(
            new DamageProfile('token-a', 2, 6, 4),
            true
        );

        self::assertCount(4, $damage->rolls());
        self::assertSame(16, $damage->total());
        self::assertTrue($damage->critical());
    }
}
