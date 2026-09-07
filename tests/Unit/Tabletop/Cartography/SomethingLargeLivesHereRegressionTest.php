<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class SomethingLargeLivesHereRegressionTest extends TestCase
{
    private function source(string $path): string
    {
        return (string) file_get_contents(dirname(__DIR__, 4) . '/' . $path);
    }

    public function test_both_forge_surfaces_offer_a_boss_lair_option(): void
    {
        $view = $this->source('app/Tabletop/Views/chamber.php');
        self::assertStringContainsString('data-atlas-forge-lair', $view);
        self::assertStringContainsString('data-dungeon-forge-lair', $view);
        self::assertStringContainsString('Include Boss Lair', $view);
        self::assertStringContainsString('Grand Dungeon only', $view);
    }

    public function test_lair_option_is_only_enabled_for_grand_dungeons(): void
    {
        $js = $this->source('assets/js/tabletop.js');
        self::assertStringContainsString("=== 'dungeon'", $js);
        self::assertStringContainsString("=== 'grand'", $js);
        self::assertStringContainsString('dungeonForgeLair.disabled = !allowed', $js);
        self::assertStringContainsString('atlasForgeLair.disabled = !allowed', $js);
    }

    public function test_grand_dungeon_reserves_a_large_semantic_lair_before_room_packing(): void
    {
        $js = $this->source('assets/js/tabletop.js');
        self::assertStringContainsString("const wantsBossLair = style === 'grand' && includeBossLair === true;", $js);
        self::assertStringContainsString("role:'lair'", $js);
        self::assertStringContainsString("boss_lair:true", $js);
        self::assertStringContainsString('Math.max(10, Math.min(14', $js);
        self::assertStringContainsString('Math.max(8, Math.min(11', $js);
    }

    public function test_server_preserves_only_the_bounded_lair_room_role(): void
    {
        $php = $this->source('app/Tabletop/Http/DungeonForgeAjaxController.php');
        self::assertStringContainsString("in_array(\$role, ['', 'lair'], true)", $php);
        self::assertStringContainsString("'boss_lair' => \$role === 'lair'", $php);
    }

    public function test_furnisher_respects_declared_lair_role(): void
    {
        $php = $this->source('app/Tabletop/Cartography/Services/ForgeFurniturePlanner.php');
        self::assertStringContainsString("\$declaredRole === 'lair'", $php);
        self::assertStringContainsString("? 'lair'", $php);
    }

    public function test_lair_furniture_hugs_the_perimeter_and_leaves_the_centre_clear(): void
    {
        $php = $this->source('app/Tabletop/Cartography/Services/ForgeFurniturePlanner.php');
        self::assertStringContainsString("'lair' => [", $php);
        self::assertStringContainsString("'x' => \$left, 'y' => \$top", $php);
        self::assertStringContainsString("'x' => \$right, 'y' => \$bottom", $php);
        self::assertStringNotContainsString("'kind' => 'table', 'x' => \$cx, 'y' => \$cy, 'rotation' => \$longRotation],\n            ],\n            'mess'", $php);
    }

    public function test_custom_furniture_can_be_labelled_for_boss_lairs(): void
    {
        $php = $this->source('app/Tabletop/SceneObjects/Admin/FurnitureCatalogueAdmin.php');
        self::assertStringContainsString("'cache', 'lair'", $php);
        self::assertStringContainsString("'lair' => 'Boss lair'", $php);
    }

    public function test_atlas_passes_the_lair_choice_into_the_shared_generator(): void
    {
        $js = $this->source('assets/js/tabletop.js');
        self::assertStringContainsString('Boolean(atlasForgeLair?.checked)', $js);
        self::assertStringContainsString('includeBossLair = false', $js);
        self::assertStringContainsString('preferredAspect, mode, includeBossLair', $js);
    }
}
