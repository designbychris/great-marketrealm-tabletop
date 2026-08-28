<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Satchel;

use GreatMarketrealmTabletop\Tabletop\Battle\Contracts\D20Roller;
use GreatMarketrealmTabletop\Tabletop\Battle\Contracts\DamageDieRoller;
use GreatMarketrealmTabletop\Tabletop\Satchel\Services\WeaponHandsRoller;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class WeaponHandsRollerTest extends TestCase
{
    /** @return array<string,mixed> */
    private function character(): array
    {
        return ['play' => ['attacks' => [[
            'id' => 'market-cleaver', 'label' => 'Market Cleaver',
            'attack_bonus' => 4, 'damage_die' => '1d6',
            'critical_damage_die' => '2d6', 'damage_modifier' => 2,
            'damage_type' => 'slashing',
        ]]]];
    }

    private function roller(int $d20 = 13, int $damage = 5): WeaponHandsRoller
    {
        return new WeaponHandsRoller(
            new class($d20) implements D20Roller { public function __construct(private int $roll) {} public function roll(): int { return $this->roll; } },
            new class($damage) implements DamageDieRoller { public function __construct(private int $roll) {} public function roll(int $sides): int { return min($sides, $this->roll); } }
        );
    }

    public function testAttackUsesTheCompanionProjectedAttackBonus(): void
    {
        $roll = $this->roller()->roll($this->character(), 'attack', 'market-cleaver');
        self::assertSame(13, $roll['die']);
        self::assertSame(4, $roll['modifier']);
        self::assertSame(17, $roll['total']);
    }

    public function testDamageUsesTheCompanionProjectedFormulaAndModifier(): void
    {
        $roll = $this->roller(13, 5)->roll($this->character(), 'damage', 'market-cleaver');
        self::assertSame([5], $roll['rolls']);
        self::assertSame(2, $roll['modifier']);
        self::assertSame(7, $roll['total']);
        self::assertSame('slashing', $roll['damage_type']);
    }

    public function testUnknownWeaponIsRejectedInsteadOfAcceptingBrowserSuppliedCombatMath(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->roller()->roll($this->character(), 'attack', 'plus-ninety-nine-sword');
    }
}
