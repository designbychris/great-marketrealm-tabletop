<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class SemanticContinuityRegionTopologyRegressionTest extends TestCase
{
    private function root(string $relative): string
    {
        return dirname(__DIR__, 4) . '/' . ltrim($relative, '/');
    }

    public function test_hybrid_reasons_about_connected_boundary_topology(): void
    {
        $script = (string) file_get_contents($this->root('assets/js/tabletop.js'));
        self::assertStringContainsString('IV.30.1G.5C — Semantic Continuity & Region Topology', $script);
        self::assertStringContainsString('const contourTopologyEvidence = (item) => {', $script);
        self::assertStringContainsString("regionMembership: coherent ? 'connected-playable-boundary' : 'local-evidence'", $script);
        self::assertStringContainsString('semanticRegionTopology: topologyEvidence.regionMembership', $script);
    }

    public function test_playable_side_consistency_can_preserve_a_weak_span_inside_a_coherent_chain(): void
    {
        $script = (string) file_get_contents($this->root('assets/js/tabletop.js'));
        self::assertStringContainsString('const playableSideConsistency = sampledSpans > 0 ? consistentSamples / sampledSpans : 0;', $script);
        self::assertStringContainsString('if (topologyEvidence?.coherent && topologyEvidence.playableSideConsistency >= .58) return false;', $script);
        self::assertStringContainsString('semanticContinuity: topologyEvidence.continuity', $script);
    }

    public function test_thresholds_remain_topological_breaks_instead_of_being_sealed(): void
    {
        $script = (string) file_get_contents($this->root('assets/js/tabletop.js'));
        self::assertStringContainsString('const thresholdNearSpan = (a, b) => {', $script);
        self::assertStringContainsString('if (thresholdNearSpan(a, b)) return false;', $script);
        self::assertStringContainsString('topology may continue a wall, never seal an', $script);
    }

    public function test_phase_is_documented(): void
    {
        $roadmap = (string) file_get_contents($this->root('ROADMAP.md'));
        $phase = (string) file_get_contents($this->root('docs/Roadmap/PHASE-IV.30.1G.5C.md'));
        self::assertStringContainsString('[x] **IV.30.1G.5C — Semantic Continuity & Region Topology**', $roadmap);
        self::assertStringContainsString('The Wall Remembers Where It Was Going', $phase);
    }
}
