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

    public function testProfileCarriesAttackRanges(): void
    {
        $profile = new CombatProfile(
            'token-a',
            15,
            4,
            30,
            120
        );

        self::assertSame(30, $profile->attackRangeFeet());
        self::assertSame(120, $profile->longRangeFeet());
        self::assertTrue($profile->isRangedAttack());
    }

    public function testLegacyProfileDefaultsToFiveFootMeleeReach(): void
    {
        $profile = CombatProfile::reconstitute([
            'token_id' => 'token-a',
            'armor_class' => 12,
            'attack_modifier' => 2,
        ]);

        self::assertSame(5, $profile->attackRangeFeet());
        self::assertSame(5, $profile->longRangeFeet());
        self::assertFalse($profile->isRangedAttack());
    }

    public function testLongRangeCannotBeShorterThanNormalRange(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new CombatProfile(
            'token-a',
            10,
            0,
            30,
            20
        );
    }

}
