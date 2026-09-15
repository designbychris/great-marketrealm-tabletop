<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class InteriorOccupancyIllustratedFloorReasoningRegressionTest extends TestCase
{
    private function root(string $relative): string
    {
        return dirname(__DIR__, 4) . '/' . ltrim($relative, '/');
    }

    public function test_illustrated_interior_surface_is_inferred_before_occlusion_recovery(): void
    {
        $script = (string) file_get_contents($this->root('assets/js/tabletop.js'));
        self::assertStringContainsString('IV.30.1G.5K — Interior Occupancy & Illustrated Floor Reasoning', $script);
        self::assertStringContainsString('const illustratedInteriorPlayableSurface =', $script);
        self::assertStringContainsString('const interiorOccupancyPass = () => {', $script);
        self::assertStringContainsString('const inferredIllustratedInteriorCells = options.skipOcclusionRecovery === true ? 0 : interiorOccupancyPass();', $script);
        self::assertLessThan(
            strpos($script, 'IV.30.1G.5J — Playable Surface Reconstruction & Occlusion Recovery'),
            strpos($script, 'IV.30.1G.5K — Interior Occupancy & Illustrated Floor Reasoning')
        );
    }

    public function test_inference_requires_semantic_enclosure_not_merely_busy_ink(): void
    {
        $script = (string) file_get_contents($this->root('assets/js/tabletop.js'));
        self::assertStringContainsString('const directionalSupport = Number(north) + Number(south) + Number(west) + Number(east);', $script);
        self::assertStringContainsString('const opposedEnclosure = (north && south) || (west && east);', $script);
        self::assertStringContainsString('if (directionalSupport < 3 || !opposedEnclosure) continue;', $script);
        self::assertStringContainsString('if (localFloorSupport < 2 || horizontalWallBand || verticalWallBand) continue;', $script);
    }

    public function test_sustained_wall_bands_are_not_reclassified_as_interior_occupancy(): void
    {
        $script = (string) file_get_contents($this->root('assets/js/tabletop.js'));
        self::assertStringContainsString('if (ink < .20 || ink > .70) continue;', $script);
        self::assertStringContainsString('const horizontalWallBand = neighbours[0].darkness >= .72 && neighbours[1].darkness >= .72;', $script);
        self::assertStringContainsString('const verticalWallBand = neighbours[2].darkness >= .72 && neighbours[3].darkness >= .72;', $script);
    }

    public function test_phase_is_documented(): void
    {
        $roadmap = (string) file_get_contents($this->root('ROADMAP.md'));
        $phase = (string) file_get_contents($this->root('docs/Roadmap/PHASE-IV.30.1G.5K.md'));
        self::assertStringContainsString('[x] **IV.30.1G.5K — Interior Occupancy & Illustrated Floor Reasoning**', $roadmap);
        self::assertStringContainsString('The Mushroom Must Be Standing Somewhere', $phase);
        self::assertStringContainsString('G.5J.1\'s monotonic contract remains authoritative', $phase);
    }
}
