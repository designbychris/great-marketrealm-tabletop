<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class PlayableSurfaceReconstructionOcclusionRecoveryRegressionTest extends TestCase
{
    private function root(string $relative): string
    {
        return dirname(__DIR__, 4) . '/' . ltrim($relative, '/');
    }

    public function test_occluded_floor_is_reconstructed_before_boundary_side_reasoning(): void
    {
        $script = (string) file_get_contents($this->root('assets/js/tabletop.js'));
        self::assertStringContainsString('IV.30.1G.5J — Playable Surface Reconstruction & Occlusion Recovery', $script);
        self::assertStringContainsString('const reconstructedPlayableSurface =', $script);
        self::assertStringContainsString('const occlusionRecoveryPass = () => {', $script);
        self::assertStringContainsString('const recoveredPlayableSurfaceCells = occlusionRecoveryPass();', $script);
    }

    public function test_recovery_requires_two_shores_and_lateral_floor_support(): void
    {
        $script = (string) file_get_contents($this->root('assets/js/tabletop.js'));
        self::assertStringContainsString('const maximumOcclusionSpan = Math.max(2, Math.round(contourSubdivisions * .85));', $script);
        self::assertStringContainsString('if (!floor[endRow][endColumn]) continue;', $script);
        self::assertStringContainsString('const supportedOcclusion = lateralSupport >=', $script);
        self::assertStringContainsString('if (!sustainedWallBand && supportedOcclusion && averageDarkness <= .62)', $script);
    }

    public function test_sustained_structural_ink_vetoes_surface_recovery(): void
    {
        $script = (string) file_get_contents($this->root('assets/js/tabletop.js'));
        self::assertStringContainsString('if (darkness[y][x] >= .72) veryDark += 1;', $script);
        self::assertStringContainsString('const sustainedWallBand = veryDark >=', $script);
        self::assertStringContainsString("'reconstructed-playable-surface'", $script);
    }

    public function test_phase_is_documented(): void
    {
        $roadmap = (string) file_get_contents($this->root('ROADMAP.md'));
        $phase = (string) file_get_contents($this->root('docs/Roadmap/PHASE-IV.30.1G.5J.md'));
        self::assertStringContainsString('[x] **IV.30.1G.5J — Playable Surface Reconstruction & Occlusion Recovery**', $roadmap);
        self::assertStringContainsString('The Mushroom Has Not Eaten the Floor', $phase);
    }
}
