<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class TreesAreNotRoomsRegressionTest extends TestCase
{
    private function source(string $path): string
    {
        return (string) file_get_contents(dirname(__DIR__, 4) . '/' . $path);
    }

    public function test_outdoor_surfaces_do_not_use_dungeon_floor_ink_or_decorations(): void
    {
        $js = $this->source('assets/js/tabletop.js');
        self::assertStringContainsString("const outdoorScene = ['forest', 'village'].includes", $js);
        self::assertStringContainsString('if (outdoorScene)', $js);
        self::assertStringContainsString('if (!outdoorScene) plan.floor.forEach', $js);
    }

    public function test_trees_are_rendered_as_organic_canopies_not_feature_rectangles(): void
    {
        $js = $this->source('assets/js/tabletop.js');
        $css = $this->source('assets/css/tabletop.css');
        self::assertStringContainsString('const organicCanopy = (feature)', $js);
        self::assertStringContainsString("kind === 'tree-cluster' || kind === 'village-tree'", $js);
        self::assertStringContainsString('is-pixel-canopy', $js);
        self::assertStringContainsString('is-canopy-pixel', $js);
        self::assertStringContainsString('.is-pixel-canopy .is-canopy-pixel', $css);
        self::assertStringNotContainsString('is-canopy-blob', $js);
    }

    public function test_village_buildings_have_building_specific_visual_vocabulary(): void
    {
        $js = $this->source('assets/js/tabletop.js');
        self::assertStringContainsString("['house','cottage','workshop','inn'].includes(kind)", $js);
        self::assertStringContainsString('is-building-roof', $js);
        self::assertStringContainsString('is-building-body', $js);
    }
}
