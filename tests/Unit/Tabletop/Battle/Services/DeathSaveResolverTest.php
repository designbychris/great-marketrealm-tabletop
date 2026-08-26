<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Battle\Services;

use GreatMarketrealmTabletop\Tabletop\Battle\Contracts\DeathSaveRoller;
use GreatMarketrealmTabletop\Tabletop\Battle\Services\DeathSaveResolver;
use PHPUnit\Framework\TestCase;

final class DeathSaveResolverTest extends TestCase
{
    public function testResolverUsesInjectedRoller(): void
    {
        $roller = new class implements DeathSaveRoller {
            public function roll(): int
            {
                return 20;
            }
        };

        $outcome = (new DeathSaveResolver($roller))->resolve();

        self::assertSame(20, $outcome->roll());
        self::assertTrue($outcome->revives());
    }
}
