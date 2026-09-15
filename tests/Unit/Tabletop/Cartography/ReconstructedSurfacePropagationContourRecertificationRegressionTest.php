<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class ReconstructedSurfacePropagationContourRecertificationRegressionTest extends TestCase
{
    private function root(string $relative): string
    {
        return dirname(__DIR__, 4) . '/' . ltrim($relative, '/');
    }

    public function test_reconstructed_and_illustrated_surface_provenance_reaches_region_closure(): void
    {
        $script = (string) file_get_contents($this->root('assets/js/tabletop.js'));
        self::assertStringContainsString('IV.30.1G.5L — Reconstructed Surface Propagation & Contour Re-certification', $script);
        self::assertStringContainsString('const propagatedRecoveredSurface =', $script);
        self::assertStringContainsString('reconstructedPlayableSurface[row][column] || illustratedInteriorPlayableSurface[row][column]', $script);
        self::assertStringContainsString('const propagatedRecovery = propagatedRecoveredSurface[row][column];', $script);
    }

    public function test_propagated_surface_requires_local_playable_support_before_recertification(): void
    {
        $script = (string) file_get_contents($this->root('assets/js/tabletop.js'));
        self::assertStringContainsString('if (!closureRecovered && !propagatedRecovery) return;', $script);
        self::assertStringContainsString('const orthogonalSupport = [[-1,0],[1,0],[0,-1],[0,1]]', $script);
        self::assertStringContainsString('if (orthogonalSupport < 2) return;', $script);
        self::assertStringContainsString('const threshold = contourThresholdMatch(a, b);', $script);
        self::assertStringContainsString('if (threshold) return;', $script);
    }

    public function test_recertified_perimeter_is_explicitly_additive_recovery_evidence(): void
    {
        $script = (string) file_get_contents($this->root('assets/js/tabletop.js'));
        self::assertStringContainsString('reconstructedSurfacePropagation: true, contourRecertification: true', $script);
        self::assertStringContainsString("'propagated-recovery-evidence'", $script);
        self::assertStringContainsString("evidenceModel: 'living-contour-reconstructed-surface-propagation-v9'", $script);
        self::assertStringContainsString('Monotonic evidence contract: preserve every pre-G.5J certified object first', $script);
    }

    public function test_phase_is_documented(): void
    {
        $roadmap = (string) file_get_contents($this->root('ROADMAP.md'));
        $phase = (string) file_get_contents($this->root('docs/Roadmap/PHASE-IV.30.1G.5L.md'));
        self::assertStringContainsString('[x] **IV.30.1G.5L — Reconstructed Surface Propagation & Contour Re-certification**', $roadmap);
        self::assertStringContainsString('The Floor Is Finally Allowed to Inform the Wall', $phase);
        self::assertStringContainsString("G.5J.1's monotonic contract remains authoritative", $phase);
    }
}
