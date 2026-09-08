<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Bestiary;

use GreatMarketrealmTabletop\Integration\Companion\CompanionBestiarySource;
use GreatMarketrealmTabletop\Tabletop\Bestiary\Services\BestiaryCompatibilityNormalizer;
use GreatMarketrealmTabletop\Tabletop\Bestiary\Services\ExternalBestiaryMapper;
use PHPUnit\Framework\TestCase;

final class BestiaryCatalogueCompatibilityRegressionTest extends TestCase
{
    public function test_damage_vocabulary_accepts_presentation_friendly_canonical_labels(): void
    {
        $normalizer = new BestiaryCompatibilityNormalizer();
        self::assertSame('piercing', $normalizer->damageType('Piercing Damage'));
        self::assertSame('cold', $normalizer->damageType('Frost'));
        self::assertSame('lightning', $normalizer->damageType('Electricity'));
    }

    public function test_unknown_damage_vocabulary_names_the_actual_value(): void
    {
        $normalizer = new BestiaryCompatibilityNormalizer();
        $this->expectExceptionMessage('Unsupported Bestiary damage type "vegetable".');
        $normalizer->damageType('vegetable');
    }

    public function test_attack_kind_vocabulary_accepts_companion_style_labels(): void
    {
        $normalizer = new BestiaryCompatibilityNormalizer();
        self::assertSame('melee-weapon', $normalizer->attackKind('Melee Weapon Attack'));
        self::assertSame('ranged-weapon', $normalizer->attackKind('Ranged Weapon Attack'));
        self::assertSame('spell', $normalizer->attackKind('Spell Attack'));
    }

    public function test_mapper_accepts_camel_case_and_nested_battlefield_measures(): void
    {
        $mapper = new ExternalBestiaryMapper();
        $creature = $mapper->map([
            'slug' => 'kale-hydra',
            'label' => 'Kale Hydra',
            'stats' => ['ac' => 15, 'hp' => 88, 'speed' => 30],
            'attacks' => [[
                'label' => 'Bite',
                'type' => 'Melee Weapon Attack',
                'damage_type' => 'Piercing Damage',
            ]],
        ]);
        self::assertNotNull($creature);
        self::assertSame('kale-hydra', $creature->id());
        self::assertSame(15, $creature->armorClass());
        self::assertSame(88, $creature->hitPoints());
        self::assertSame('piercing', $creature->attacks()[0]['damage']['type']);
    }

    public function test_mapper_no_longer_drops_records_just_because_common_aliases_are_used(): void
    {
        $mapper = new ExternalBestiaryMapper();
        self::assertNotNull($mapper->map([
            'key' => 'alias-beast',
            'name' => 'Alias Beast',
            'armorClass' => 12,
            'hitPoints' => 7,
            'speedFeet' => 25,
        ]));
    }

    public function test_companion_source_declares_published_and_supplemental_shelves(): void
    {
        $source = (string) file_get_contents(
            dirname(__DIR__, 4) . '/app/Integration/Companion/CompanionBestiarySource.php'
        );
        self::assertStringContainsString('gmrc_tabletop_bestiary_records', $source);
        self::assertStringContainsString('gmrc_tabletop_bestiary_supplemental_records', $source);
        self::assertStringContainsString('gmrc_tabletop_bestiary_workshop_records', $source);
    }

    public function test_companion_source_deduplicates_shelves_by_stable_identity(): void
    {
        $source = (string) file_get_contents(
            dirname(__DIR__, 4) . '/app/Integration/Companion/CompanionBestiarySource.php'
        );
        self::assertStringContainsString('$records[$id] = $record;', $source);
        self::assertStringContainsString("['id'] ?? \$record['key'] ?? \$record['slug']", $source);
    }

    public function test_combat_provisioner_uses_compatibility_boundary_for_damage_and_attack_kind(): void
    {
        $source = (string) file_get_contents(
            dirname(__DIR__, 4) . '/app/Tabletop/Bestiary/Services/BestiaryCombatProvisioner.php'
        );
        self::assertStringContainsString('$this->compatibility->damageType(', $source);
        self::assertStringContainsString('$this->compatibility->attackKind(', $source);
    }
}
