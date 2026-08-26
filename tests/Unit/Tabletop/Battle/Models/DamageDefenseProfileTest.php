<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Battle\Models;

use GreatMarketrealmTabletop\Tabletop\Battle\Models\DamageDefenseProfile;
use PHPUnit\Framework\TestCase;

final class DamageDefenseProfileTest extends TestCase
{
    public function testProfileRecognizesEachDefenseKind(): void
    {
        $profile = new DamageDefenseProfile(
            'token-a',
            ['slashing'],
            ['fire'],
            ['poison']
        );

        self::assertTrue($profile->resists('slashing'));
        self::assertTrue($profile->vulnerableTo('fire'));
        self::assertTrue($profile->immuneTo('poison'));
    }

    public function testProfileRoundTrips(): void
    {
        $profile = new DamageDefenseProfile(
            'token-a',
            ['cold'],
            ['fire'],
            ['poison']
        );

        self::assertSame(
            $profile->toArray(),
            DamageDefenseProfile::reconstitute(
                $profile->toArray()
            )->toArray()
        );
    }
}
