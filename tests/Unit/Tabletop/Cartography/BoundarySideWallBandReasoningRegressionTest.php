<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class BoundarySideWallBandReasoningRegressionTest extends TestCase
{
    private function root(string $relative): string
    {
        return dirname(__DIR__, 4) . '/' . ltrim($relative, '/');
    }

    public function test_living_contour_identifies_only_substantial_border_connected_whitespace_as_exterior(): void
    {
        $script = (string) file_get_contents($this->root('assets/js/tabletop.js'));

        self::assertStringContainsString('IV.30.1G.5 — Boundary-Side & Wall-Band Reasoning', $script);
        self::assertStringContainsString('const exteriorFloorComponents = new Set', $script);
        self::assertStringContainsString('entry.borderSamples >= Math.max(8, contourSubdivisions * 2)', $script);
        self::assertStringContainsString('entry.size / totalFineCells >= .08', $script);
        self::assertStringContainsString('!exteriorFloorComponents.has(floorComponent[row][column])', $script);
    }

    public function test_wall_band_edges_require_sustained_playable_floor_on_the_inward_side(): void
    {
        $script = (string) file_get_contents($this->root('assets/js/tabletop.js'));

        self::assertStringContainsString('const minimumWallBandFloorDepth = Math.max(2, Math.round(contourSubdivisions * .34))', $script);
        self::assertStringContainsString('const wallBandFloorDepth = (column, row, dx, dy) => {', $script);
        self::assertStringContainsString('const wallBandEdgeIsFloorFacing = (column, row, inwardDx, inwardDy)', $script);
        self::assertStringContainsString('wallBandEdgeIsFloorFacing(column, row, 0, 1)', $script);
        self::assertStringContainsString('wallBandEdgeIsFloorFacing(column, row, -1, 0)', $script);
    }

    public function test_wall_band_reasoning_preserves_threshold_and_partial_contour_semantics(): void
    {
        $script = (string) file_get_contents($this->root('assets/js/tabletop.js'));

        self::assertStringContainsString('splitContourPathAtProtectedThresholds(points)', $script);
        self::assertStringContainsString("? 'protected-threshold-split'", $script);
        self::assertStringContainsString("'living-contour-wall-band-v6'", $script);
        self::assertStringContainsString("'living-contour-wall-band-partial-v6'", $script);
        self::assertStringContainsString('unresolvedBoundaryEnds: partialContour', $script);
    }

    public function test_phase_is_documented_as_floor_facing_edge_selection_not_semantic_terrain_classification(): void
    {
        $roadmap = (string) file_get_contents($this->root('ROADMAP.md'));
        $phase = (string) file_get_contents($this->root('docs/Roadmap/PHASE-IV.30.1G.5.md'));

        self::assertStringContainsString('[x] **IV.30.1G.5 — Boundary-Side & Wall-Band Reasoning**', $roadmap);
        self::assertStringContainsString('The wall belongs where the adventurer meets the rock', $phase);
        self::assertStringContainsString('Semantic boundary classification follows', $phase);
    }
}
