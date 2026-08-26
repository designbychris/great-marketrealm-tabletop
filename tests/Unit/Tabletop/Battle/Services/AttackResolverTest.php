<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Battle\Services;

use GreatMarketrealmTabletop\Tabletop\Battle\Contracts\D20Roller;
use GreatMarketrealmTabletop\Tabletop\Battle\Models\AttackOutcome;
use GreatMarketrealmTabletop\Tabletop\Battle\Models\CombatProfile;
use GreatMarketrealmTabletop\Tabletop\Battle\Services\AttackResolver;
use PHPUnit\Framework\TestCase;

final class AttackResolverTest extends TestCase
{
    public function testResolverUsesInjectedD20AndProfiles(): void
    {
        $roller = new class implements D20Roller {
            public function roll(): int
            {
                return 13;
            }
        };

        $outcome = (new AttackResolver($roller))->resolve(
            new CombatProfile('attacker', 10, 4),
            new CombatProfile('target', 17, 0)
        );

        self::assertSame(13, $outcome->toArray()['roll']);
        self::assertSame(17, $outcome->toArray()['total']);
        self::assertSame(AttackOutcome::HIT, $outcome->result());
    }
}
