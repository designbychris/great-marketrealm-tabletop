<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Satchel;

use GreatMarketrealmTabletop\Tabletop\Battle\Contracts\D20Roller;
use GreatMarketrealmTabletop\Tabletop\Battle\Contracts\DamageDieRoller;
use GreatMarketrealmTabletop\Tabletop\Satchel\Services\SpellPouchRoller;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class SpellPouchRollerTest extends TestCase
{
    /** @return array<string,mixed> */
    private function character(): array
    {
        return ['play' => ['spellcasting' => ['spells' => [
            [
                'id' => 'vine-lash',
                'label' => 'Vine Lash',
                'spell_attack' => 2,
                'roll_kind' => 'damage',
                'formula' => '1d6',
                'roll_modifier' => 0,
                'damage_type' => 'piercing',
            ],
            [
                'id' => 'cure-wounds',
                'label' => 'Cure Meats',
                'spell_attack' => null,
                'roll_kind' => 'healing',
                'formula' => '1d8',
                'roll_modifier' => 3,
                'damage_type' => '',
            ],
        ]]]];
    }

    private function roller(int $d20 = 13, int $spellDie = 5): SpellPouchRoller
    {
        return new SpellPouchRoller(
            new class($d20) implements D20Roller {
                public function __construct(private int $roll) {}
                public function roll(): int { return $this->roll; }
            },
            new class($spellDie) implements DamageDieRoller {
                public function __construct(private int $roll) {}
                public function roll(int $sides): int { return min($sides, $this->roll); }
            }
        );
    }

    public function testSpellAttackUsesTheCompanionProjectedSpellAttackBonus(): void
    {
        $roll = $this->roller()->roll($this->character(), 'attack', 'vine-lash');
        self::assertSame(13, $roll['die']);
        self::assertSame(2, $roll['modifier']);
        self::assertSame(15, $roll['total']);
    }

    public function testDamageUsesTheCompanionProjectedFormulaModifierAndType(): void
    {
        $roll = $this->roller(13, 4)->roll($this->character(), 'damage', 'vine-lash');
        self::assertSame([4], $roll['rolls']);
        self::assertSame(0, $roll['modifier']);
        self::assertSame(4, $roll['total']);
        self::assertSame('piercing', $roll['damage_type']);
    }

    public function testHealingUsesTheCompanionProjectedFormulaAndCastingModifier(): void
    {
        $roll = $this->roller(13, 6)->roll($this->character(), 'healing', 'cure-wounds');
        self::assertSame([6], $roll['rolls']);
        self::assertSame(3, $roll['modifier']);
        self::assertSame(9, $roll['total']);
        self::assertSame('healing', $roll['action']);
    }

    public function testSpellActionCannotBeChangedIntoAHealingRollByTheBrowser(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->roller()->roll($this->character(), 'healing', 'vine-lash');
    }
}
