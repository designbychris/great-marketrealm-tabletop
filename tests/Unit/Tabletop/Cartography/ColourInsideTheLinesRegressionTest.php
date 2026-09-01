<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class ColourInsideTheLinesRegressionTest extends TestCase
{
    private function source(string $path): string
    {
        return (string) file_get_contents(dirname(__DIR__, 4) . '/' . $path);
    }

    public function test_outdoor_forge_barriers_are_hidden_by_persisted_forge_ownership_not_removed(): void
    {
        $js = $this->source('assets/js/tabletop.js');
        $css = $this->source('assets/css/tabletop.css');

        self::assertStringContainsString('const forgeEnvironmentalBarrierIds = () =>', $js);
        self::assertStringContainsString("['forest', 'village'].includes", $js);
        self::assertStringContainsString('plan.barrier_ids', $js);
        self::assertStringContainsString("shape.classList.add('is-forge-environmental')", $js);
        self::assertStringContainsString('.gmrt-vision-barrier.is-forge-environmental:not(.is-selected)', $css);
        self::assertStringContainsString('opacity: 0;', $css);
    }

    public function test_environmental_barrier_can_still_reveal_when_keeper_selects_it(): void
    {
        $css = $this->source('assets/css/tabletop.css');
        self::assertStringContainsString('.gmrt-vision-barrier.is-forge-environmental.is-selected', $css);
    }

    public function test_outdoor_svg_has_richer_non_rectangular_visual_vocabulary(): void
    {
        $js = $this->source('assets/js/tabletop.js');
        $css = $this->source('assets/css/tabletop.css');

        self::assertStringContainsString('const organicRocks = (feature)', $js);
        self::assertStringContainsString('const villageBuilding = (feature, kind)', $js);
        self::assertStringContainsString('const fallenLog = (feature)', $js);
        self::assertStringContainsString('const villageWell = (feature)', $js);
        self::assertStringContainsString('const fencedGarden = (feature)', $js);
        self::assertStringContainsString('is-canopy-highlight', $css);
        self::assertStringContainsString('is-building-window', $css);
        self::assertStringContainsString('is-log-bark', $css);
        self::assertStringContainsString('is-well-water', $css);
        self::assertStringContainsString('is-garden-row', $css);
    }
}
