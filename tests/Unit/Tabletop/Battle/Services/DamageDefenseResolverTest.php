<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Battle\Services;

use GreatMarketrealmTabletop\Tabletop\Battle\Models\DamageDefenseProfile;
use GreatMarketrealmTabletop\Tabletop\Battle\Services\DamageDefenseResolver;
use PHPUnit\Framework\TestCase;

final class DamageDefenseResolverTest extends TestCase
{
    private DamageDefenseResolver $resolver;

    protected function setUp(): void
    {
        $this->resolver = new DamageDefenseResolver();
    }

    public function testResistanceHalvesDamageRoundingDown(): void
    {
        $result = $this->resolver->resolve(
            9,
            'slashing',
            new DamageDefenseProfile(
                'token-a',
                ['slashing']
            )
        );

        self::assertSame(4, $result->resolvedDamage());
        self::assertSame(
            ['resistant'],
            $result->toArray()['effects']
        );
    }

    public function testVulnerabilityDoublesDamage(): void
    {
        $result = $this->resolver->resolve(
            7,
            'fire',
            new DamageDefenseProfile(
                'token-a',
                [],
                ['fire']
            )
        );

        self::assertSame(14, $result->resolvedDamage());
    }

    public function testImmunityOverridesOtherDefenses(): void
    {
        $result = $this->resolver->resolve(
            12,
            'poison',
            new DamageDefenseProfile(
                'token-a',
                ['poison'],
                ['poison'],
                ['poison']
            )
        );

        self::assertSame(0, $result->resolvedDamage());
        self::assertSame(
            ['immune'],
            $result->toArray()['effects']
        );
    }

    public function testResistanceThenVulnerabilityHasDeterministicOrder(): void
    {
        $result = $this->resolver->resolve(
            9,
            'cold',
            new DamageDefenseProfile(
                'token-a',
                ['cold'],
                ['cold']
            )
        );

        self::assertSame(8, $result->resolvedDamage());
        self::assertSame(
            ['resistant', 'vulnerable'],
            $result->toArray()['effects']
        );
    }

    public function testNeutralDefenseLeavesDamageUntouched(): void
    {
        $result = $this->resolver->resolve(
            11,
            'force',
            new DamageDefenseProfile('token-a')
        );

        self::assertSame(11, $result->resolvedDamage());
        self::assertSame([], $result->toArray()['effects']);
    }
}
