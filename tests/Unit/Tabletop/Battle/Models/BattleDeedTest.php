<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Battle\Models;

use GreatMarketrealmTabletop\Tabletop\Battle\Models\BattleDeed;
use GreatMarketrealmTabletop\Tabletop\Battle\Models\TurnResource;
use PHPUnit\Framework\TestCase;

final class BattleDeedTest extends TestCase
{
    public function testInitialCanonicalDeedsAreStable(): void
    {
        self::assertSame(
            ['attack', 'dash', 'disengage', 'dodge', 'help'],
            BattleDeed::all()
        );
    }

    public function testInitialDeedsSpendTheActionResource(): void
    {
        foreach (BattleDeed::all() as $deed) {
            self::assertSame(
                TurnResource::ACTION,
                BattleDeed::resource($deed)
            );
        }
    }
}
