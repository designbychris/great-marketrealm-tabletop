<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Battle\Models;

use GreatMarketrealmTabletop\Tabletop\Battle\Exceptions\TurnResourceSpent;
use GreatMarketrealmTabletop\Tabletop\Battle\Models\TurnEconomy;
use GreatMarketrealmTabletop\Tabletop\Battle\Models\TurnResource;
use PHPUnit\Framework\TestCase;

final class TurnEconomyTest extends TestCase
{
    public function testFreshTurnHasNoSpentResources(): void
    {
        $economy = new TurnEconomy();

        foreach (TurnResource::all() as $resource) {
            self::assertFalse($economy->isSpent($resource));
        }
    }

    public function testResourceCannotBeSpentTwice(): void
    {
        $economy = new TurnEconomy();
        $economy->spend(TurnResource::ACTION);

        $this->expectException(TurnResourceSpent::class);
        $economy->spend(TurnResource::ACTION);
    }

    public function testResetRestoresEveryResource(): void
    {
        $economy = new TurnEconomy();
        $economy->spend(TurnResource::ACTION);
        $economy->spend(TurnResource::REACTION);
        $economy->reset();

        self::assertFalse($economy->isSpent(TurnResource::ACTION));
        self::assertFalse($economy->isSpent(TurnResource::REACTION));
    }
}
