<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Arsenal;

use GreatMarketrealmTabletop\Tabletop\Arsenal\Models\AttackKind;
use GreatMarketrealmTabletop\Tabletop\Arsenal\Services\CompanionCombatArsenalProjector;
use PHPUnit\Framework\TestCase;

final class CompanionCombatArsenalProjectorTest extends TestCase
{
    /** @return array<string,mixed> */
    private function character(): array
    {
        return [
            'id' => 'fungi-darkvision-test',
            'play' => [
                'armour_class' => 14,
                'attacks' => [
                    [
                        'id' => 'market-cleaver',
                        'label' => 'Market Cleaver',
                        'attack_bonus' => 5,
                        'damage_die' => '1d8',
                        'damage_modifier' => 3,
                        'damage_type' => 'slashing',
                        'properties' => [],
                        'range' => 'Melee · 5 ft',
                    ],
                    [
                        'id' => 'pea-shooter',
                        'label' => 'Pea Shooter',
                        'attack_bonus' => 4,
                        'damage_die' => '1d6',
                        'damage_modifier' => 2,
                        'damage_type' => 'piercing',
                        'properties' => ['ranged'],
                        'range' => 'Ranged · 80/320 ft',
                    ],
                ],
            ],
        ];
    }

    public function testCompanionWeaponsBecomeTabletopArsenalAttacks(): void
    {
        $arsenal = (new CompanionCombatArsenalProjector())->project('token-player', $this->character());

        self::assertCount(2, $arsenal->attacks());
        self::assertSame('market-cleaver', $arsenal->attacks()[0]->id());
        self::assertSame(5, $arsenal->attacks()[0]->combat()->attackModifier());
        self::assertSame('slashing', $arsenal->attacks()[0]->damage()->damageType());
    }

    public function testRangedWeaponKeepsCompanionNormalAndLongRange(): void
    {
        $attack = (new CompanionCombatArsenalProjector())
            ->project('token-player', $this->character())
            ->find('pea-shooter');

        self::assertNotNull($attack);
        self::assertSame(AttackKind::RANGED_WEAPON, $attack->kind());
        self::assertSame(80, $attack->combat()->attackRangeFeet());
        self::assertSame(320, $attack->combat()->longRangeFeet());
    }

    public function testBrowserDoesNotSupplyCompanionCombatMath(): void
    {
        $projector = file_get_contents(dirname(__DIR__, 4) . '/app/Tabletop/Arsenal/Services/CompanionCombatArsenalProjector.php');
        self::assertIsString($projector);
        self::assertStringContainsString("attack_bonus", $projector);
        self::assertStringNotContainsString('$_POST', $projector);
    }

    public function testUnsupportedCompanionDamageFormulaIsNotInvented(): void
    {
        $character = $this->character();
        $character['play']['attacks'][0]['damage_die'] = 'not-a-die';

        $arsenal = (new CompanionCombatArsenalProjector())->project('token-player', $character);

        self::assertNull($arsenal->find('market-cleaver'));
        self::assertNotNull($arsenal->find('pea-shooter'));
    }
}
