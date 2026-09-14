<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class ArchitecturalTopologyEvidenceRegressionTest extends TestCase
{
    private function root(string $relative): string
    {
        return dirname(__DIR__, 4) . '/' . ltrim($relative, '/');
    }

    public function test_structural_reader_builds_endpoint_topology_for_architectural_support(): void
    {
        $script = (string) file_get_contents($this->root('assets/js/tabletop.js'));

        self::assertStringContainsString('IV.30.1G.1 — Corners, Junctions & Wall Bodies', $script);
        self::assertStringContainsString('const vertexWalls = new Map()', $script);
        self::assertStringContainsString('const endpointArchitecture = (wall, wallIndex, x, y) =>', $script);
        self::assertStringContainsString('corner: turns >= 1 && neighbours.length === 1', $script);
        self::assertStringContainsString('junction: neighbours.length >= 2', $script);
        self::assertStringContainsString('continuation', $script);
    }

    public function test_wall_body_profile_adds_support_without_becoming_a_hard_requirement(): void
    {
        $script = (string) file_get_contents($this->root('assets/js/tabletop.js'));

        self::assertStringContainsString('const wallBodyProfile = (wall) =>', $script);
        self::assertStringContainsString('const innerSupport = Math.max(innerA, innerB)', $script);
        self::assertStringContainsString('const quietExterior = Math.min(outerA, outerB)', $script);
        self::assertStringContainsString("if (body.supported) evidence.push('wall-body')", $script);
        self::assertStringContainsString("if (isolated) evidence.push('isolated-stroke')", $script);
        self::assertStringContainsString('- (isolated ? 8 : 0)', $script);
    }

    public function test_architectural_evidence_adjusts_confidence_but_preserves_review_first_drafts(): void
    {
        $script = (string) file_get_contents($this->root('assets/js/tabletop.js'));

        self::assertStringContainsString('const architectureBoost = (cornerCount * 4)', $script);
        self::assertStringContainsString('confidence: Math.max(42, Math.min(99, Math.round(wall.confidence + architectureBoost)))', $script);
        self::assertStringContainsString("evidenceModel: 'local-contrast-topology-v2'", $script);
        self::assertStringContainsString('const noiseScreenedWalls = architecturalWalls.map((wall) =>', $script);
        self::assertStringContainsString('return noiseScreenedWalls.concat(doorwayCandidates)', $script);
        self::assertStringContainsString('renderCartographyReview()', $script);
        self::assertStringContainsString('.filter((item) => item.selected)', $script);
    }

    public function test_phase_is_recorded_as_evidence_not_authority(): void
    {
        $roadmap = (string) file_get_contents($this->root('ROADMAP.md'));
        $phase = (string) file_get_contents($this->root('docs/Roadmap/PHASE-IV.30.1G.1.md'));

        self::assertStringContainsString('[x] **IV.30.1G.1 — Corners, Junctions & Wall Bodies**', $roadmap);
        self::assertStringContainsString('topology remains evidence rather than authority', $phase);
        self::assertStringContainsString('It is **not deleted**', $phase);
        self::assertStringContainsString('Nothing from this pass writes a wall automatically', $phase);
        self::assertStringContainsString('IV.30.1G.2', $phase);
    }
}
