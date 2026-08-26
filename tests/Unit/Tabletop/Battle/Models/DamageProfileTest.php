<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Battle\Models;

use GreatMarketrealmTabletop\Tabletop\Battle\Models\DamageProfile;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class DamageProfileTest extends TestCase
{
    public function testDefaultDamageProfileIsOneD6(): void
    {
        $profile = new DamageProfile('token-a');

        self::assertSame(1, $profile->diceCount());
        self::assertSame(6, $profile->dieSides());
        self::assertSame(0, $profile->modifier());
    }

    public function testUnsupportedDamageDieIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new DamageProfile('token-a', 1, 7, 0);
    }
}
