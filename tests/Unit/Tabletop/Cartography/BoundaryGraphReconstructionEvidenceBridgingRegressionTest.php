<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class BoundaryGraphReconstructionEvidenceBridgingRegressionTest extends TestCase
{
    private function root(string $relative): string
    {
        return dirname(__DIR__, 4) . '/' . ltrim($relative, '/');
    }

    public function test_living_contour_reconstructs_a_boundary_graph_from_certified_chain_ends(): void
    {
        $script = (string) file_get_contents($this->root('assets/js/tabletop.js'));
        self::assertStringContainsString('IV.30.1G.5H — Boundary Graph Reconstruction & Evidence Bridging', $script);
        self::assertStringContainsString('const boundaryGraphReconstruction = (entries) => {', $script);
        self::assertStringContainsString('const boundaryGraph = boundaryGraphReconstruction(recoverableChains);', $script);
        self::assertStringContainsString("classification: 'certified-evidence-bridge'", $script);
    }

    public function test_graph_bridges_require_geometry_sidedness_and_non_playable_bridge_evidence(): void
    {
        $script = (string) file_get_contents($this->root('assets/js/tabletop.js'));
        self::assertStringContainsString('if (approachA < .52 || approachB < .52 || tangentAgreement < .62) continue;', $script);
        self::assertStringContainsString('if (!boundarySideSeparation || throughPlayableRatio >= .50) continue;', $script);
        self::assertStringContainsString("'non-playable-through-bridge'", $script);
        self::assertStringContainsString("'proximity-is-evidence-not-permission'", $script);
    }

    public function test_portals_and_threshold_connected_ends_are_excluded_from_graph_bridging(): void
    {
        $script = (string) file_get_contents($this->root('assets/js/tabletop.js'));
        self::assertStringContainsString("if (entry.portalPairing?.portal || entry.playableAdjacency?.thresholdConnectivity) return;", $script);
        self::assertStringContainsString('certifiedPortal: false', $script);
        self::assertStringContainsString("evidenceModel: 'living-contour-boundary-graph-v7'", $script);
    }

    public function test_phase_is_documented(): void
    {
        $roadmap = (string) file_get_contents($this->root('ROADMAP.md'));
        $phase = (string) file_get_contents($this->root('docs/Roadmap/PHASE-IV.30.1G.5H.md'));
        self::assertStringContainsString('[x] **IV.30.1G.5H — Boundary Graph Reconstruction & Evidence Bridging**', $roadmap);
        self::assertStringContainsString('Pippin Reconstructs the Broken Wall', $phase);
    }
}
