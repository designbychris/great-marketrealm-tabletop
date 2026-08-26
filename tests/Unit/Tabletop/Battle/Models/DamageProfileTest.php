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

    public function testDamageProfileCarriesDamageType(): void
    {
        $profile = new DamageProfile(
            'token-a',
            1,
            8,
            3,
            'fire'
        );

        self::assertSame('fire', $profile->damageType());
        self::assertSame(
            'fire',
            $profile->toArray()['damage_type']
        );
    }

    public function testLegacyDamageProfileDefaultsToSlashing(): void
    {
        $profile = DamageProfile::reconstitute([
            'token_id' => 'token-a',
            'dice_count' => 1,
            'die_sides' => 6,
            'modifier' => 0,
        ]);

        self::assertSame('slashing', $profile->damageType());
    }

}
