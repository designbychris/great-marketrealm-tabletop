<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Presentation;

use PHPUnit\Framework\TestCase;

final class PippinDiscoversPixelArtRegressionTest extends TestCase
{
    private function js(): string
    {
        return (string) file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
    }

    private function css(): string
    {
        return (string) file_get_contents(dirname(__DIR__, 4) . '/assets/css/tabletop.css');
    }

    public function test_forge_projection_opts_into_crisp_pixel_art_without_changing_plan_contract(): void
    {
        $js = $this->js();
        self::assertStringContainsString("classList.add('has-plan', 'is-pixel-art')", $js);
        self::assertStringContainsString("const pixelRect = (x, y, width, height, className)", $js);
        self::assertStringContainsString("const polygon = (points, className)", $js);
        self::assertStringContainsString("plan.floor.forEach((cell)", $js);
    }

    public function test_dungeon_floor_receives_pixel_surface_marks_not_new_topology(): void
    {
        $js = $this->js();
        self::assertStringContainsString("'is-dungeon-tile-highlight'", $js);
        self::assertStringContainsString("'is-dungeon-tile-shadow'", $js);
        self::assertStringContainsString("const floorSet = new Set(plan.floor.map", $js);
    }

    public function test_forest_objects_use_block_canopies_and_faceted_rocks(): void
    {
        $js = $this->js();
        self::assertStringContainsString('is-organic-canopy is-pixel-canopy', $js);
        self::assertStringContainsString("'is-canopy-pixel'", $js);
        self::assertStringContainsString('is-organic-rocks is-pixel-rocks', $js);
        self::assertStringContainsString("polygon([[cx-r,cy+.12*r]", $js);
    }

    public function test_village_objects_keep_existing_feature_functions_but_gain_pixel_vocabulary(): void
    {
        $js = $this->js();
        self::assertStringContainsString('const villageBuilding = (feature, kind)', $js);
        self::assertStringContainsString("'is-roof-pixel-line'", $js);
        self::assertStringContainsString('is-detailed-well is-pixel-well', $js);
        self::assertStringContainsString("'is-garden-post'", $js);
    }

    public function test_pixel_scene_skin_is_crisp_and_does_not_mutate_forge_authority(): void
    {
        $css = $this->css();
        $js = $this->js();
        self::assertStringContainsString('.gmrt-dungeon-forge-layer.is-pixel-art', $css);
        self::assertStringContainsString('shape-rendering: crispEdges', $css);
        self::assertStringContainsString('stroke-linejoin: miter', $css);
        self::assertStringContainsString('forgeRandom(`${seed}|${style}|forest`)', $js);
        self::assertStringContainsString('forgeRandom(`${seed}|${style}|village`)', $js);
    }
}
