<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class PlayableSpaceAdjacencyThresholdConnectivityRegressionTest extends TestCase
{
    private function root(string $relative): string
    {
        return dirname(__DIR__, 4) . '/' . ltrim($relative, '/');
    }

    public function test_living_contour_uses_playable_space_adjacency_after_boundary_role_classification(): void
    {
        $script = (string) file_get_contents($this->root('assets/js/tabletop.js'));
        self::assertStringContainsString('IV.30.1G.5F — Playable-Space Adjacency & Threshold Connectivity', $script);
        self::assertStringContainsString('const playableSpaceAdjacency = (chain, closed, semanticBoundary) => {', $script);
        self::assertStringContainsString('.map(applyPlayableSpaceAdjacency)', $script);
        self::assertStringContainsString("classification: 'interior-playable-island'", $script);
        self::assertStringContainsString("'adjacency-demotion'", $script);
    }

    public function test_floor_continuous_boundary_gap_is_preserved_as_threshold_connectivity(): void
    {
        $script = (string) file_get_contents($this->root('assets/js/tabletop.js'));
        self::assertStringContainsString("classification: 'threshold-connected-open-chain'", $script);
        self::assertStringContainsString("'continuous-playable-floor'", $script);
        self::assertStringContainsString("'probable-threshold'", $script);
        self::assertStringContainsString('thresholdConnectivity: Boolean(entry.playableAdjacency?.thresholdConnectivity)', $script);
        self::assertStringContainsString('thresholdGapProtection: gapProtected || Boolean(entry.playableAdjacency?.thresholdConnectivity)', $script);
    }

    public function test_phase_is_documented(): void
    {
        $roadmap = (string) file_get_contents($this->root('ROADMAP.md'));
        $phase = (string) file_get_contents($this->root('docs/Roadmap/PHASE-IV.30.1G.5F.md'));
        self::assertStringContainsString('[x] **IV.30.1G.5F — Playable-Space Adjacency & Threshold Connectivity**', $roadmap);
        self::assertStringContainsString('Pippin Asks What the Wall Would Do', $phase);
    }
}
