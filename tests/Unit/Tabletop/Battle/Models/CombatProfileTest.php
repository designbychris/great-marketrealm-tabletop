<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Battle\Models;

use GreatMarketrealmTabletop\Tabletop\Battle\Models\CombatProfile;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class CombatProfileTest extends TestCase
{
    public function testDefaultProfileIsSafeAndImmediatelyUsable(): void
    {
        $profile = new CombatProfile('token-a');

        self::assertSame(10, $profile->armorClass());
        self::assertSame(0, $profile->attackModifier());
    }

    public function testProfileRoundTrips(): void
    {
        $profile = new CombatProfile('token-a', 17, 5);

        self::assertSame(
            $profile->toArray(),
            CombatProfile::reconstitute($profile->toArray())->toArray()
        );
    }

    public function testArmorClassIsBounded(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new CombatProfile('token-a', 0, 0);
    }
}
