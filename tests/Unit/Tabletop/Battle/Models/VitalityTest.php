<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Battle\Models;

use GreatMarketrealmTabletop\Tabletop\Battle\Models\Vitality;
use GreatMarketrealmTabletop\Tabletop\Battle\Models\VitalityState;
use PHPUnit\Framework\TestCase;

final class VitalityTest extends TestCase
{
    public function testTemporaryHpAbsorbsDamageBeforeCurrentHp(): void
    {
        $vitality = new Vitality('token-a', 20, 20, 5);

        $result = $vitality->damage(8);

        self::assertSame(5, $result['temporary_absorbed']);
        self::assertSame(3, $result['hp_lost']);
        self::assertSame(17, $vitality->currentHp());
        self::assertSame(0, $vitality->temporaryHp());
    }

    public function testDamageCannotReduceCurrentHpBelowZero(): void
    {
        $vitality = new Vitality('token-a', 10, 4);

        $vitality->damage(99);

        self::assertSame(0, $vitality->currentHp());
        self::assertSame(VitalityState::DOWN, $vitality->state());
    }

    public function testHealingCapsAtMaximumHp(): void
    {
        $vitality = new Vitality('token-a', 20, 8);

        self::assertSame(12, $vitality->heal(99));
        self::assertSame(20, $vitality->currentHp());
        self::assertSame(VitalityState::HEALTHY, $vitality->state());
    }

    public function testTemporaryHpKeepsOnlyTheHigherGrant(): void
    {
        $vitality = new Vitality('token-a', 20, 20, 4);

        $vitality->grantTemporaryHp(3);
        self::assertSame(4, $vitality->temporaryHp());

        $vitality->grantTemporaryHp(7);
        self::assertSame(7, $vitality->temporaryHp());
    }

    public function testPartiallyDamagedTokenIsWounded(): void
    {
        $vitality = new Vitality('token-a', 20, 19);

        self::assertSame(
            VitalityState::WOUNDED,
            $vitality->state()
        );
    }

    public function testVitalityRoundTripsPersistentRecord(): void
    {
        $vitality = new Vitality('token-a', 27, 18, 3);

        self::assertSame(
            $vitality->toArray(),
            Vitality::reconstitute($vitality->toArray())->toArray()
        );
    }

    public function testNaturalTwentyRevivalCanRestoreOneHp(): void
    {
        $vitality = new Vitality('token-a', 20, 0);
        $vitality->reviveAtOneHp();

        self::assertSame(1, $vitality->currentHp());
        self::assertSame(VitalityState::WOUNDED, $vitality->state());
    }


    public function testDamageReportsExcessBeyondZeroHp(): void
    {
        $vitality = new Vitality('token-a', 10, 4);

        $result = $vitality->damage(15);

        self::assertSame(11, $result['excess_damage']);
        self::assertSame(0, $vitality->currentHp());
    }

    public function testTemporaryHpReducesMassiveDamageOverflow(): void
    {
        $vitality = new Vitality('token-a', 10, 10, 5);

        $result = $vitality->damage(22);

        self::assertSame(7, $result['excess_damage']);
        self::assertSame(5, $result['temporary_absorbed']);
    }

}
