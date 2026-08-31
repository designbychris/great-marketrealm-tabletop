<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class ContourTopologyGuardRegressionTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        $this->root = dirname(__DIR__, 4);
    }

    public function test_adaptive_simplification_has_a_named_topology_guard(): void
    {
        $js = file_get_contents($this->root . '/assets/js/tabletop.js');
        self::assertIsString($js);
        self::assertStringContainsString('IV.30.1B.3A — Contour Topology Guard', $js);
        self::assertStringContainsString('topologySafeSpan', $js);
    }

    public function test_replacement_chords_are_bounded_to_local_gameplay_scale(): void
    {
        $js = file_get_contents($this->root . '/assets/js/tabletop.js');
        self::assertIsString($js);
        self::assertStringContainsString('maximumTopologyChord = 6', $js);
        self::assertStringContainsString('chordLength > maximumTopologyChord', $js);
        self::assertStringContainsString('columns + contourStep', $js);
        self::assertStringContainsString('rows + contourStep', $js);
    }

    public function test_winding_boundary_spans_cannot_be_flattened_by_large_budget_tolerance(): void
    {
        $js = file_get_contents($this->root . '/assets/js/tabletop.js');
        self::assertIsString($js);
        self::assertStringContainsString('maximumTopologyDeviation = .8', $js);
        self::assertStringContainsString('maximumContourDetourRatio = 1.75', $js);
        self::assertStringContainsString('contourDetourRatio <= maximumContourDetourRatio', $js);
        self::assertStringContainsString('topologySafeSpan(points) &&', $js);
    }

    public function test_overlong_straight_contours_split_instead_of_becoming_cross_map_chords(): void
    {
        $js = file_get_contents($this->root . '/assets/js/tabletop.js');
        self::assertIsString($js);
        self::assertStringContainsString('perfectly straight but over-long span', $js);
        self::assertStringContainsString('furthestIndex = Math.floor(points.length / 2)', $js);
    }

    public function test_roadmap_and_phase_document_the_topology_corrective(): void
    {
        $roadmap = file_get_contents($this->root . '/ROADMAP.md');
        $phase = file_get_contents($this->root . '/docs/Roadmap/PHASE-IV.30.1B.3A.md');
        self::assertIsString($roadmap);
        self::assertIsString($phase);
        self::assertStringContainsString('[x] **IV.30.1B.3A — Contour Topology Guard**', $roadmap);
        self::assertStringContainsString('six gameplay-grid units', $phase);
        self::assertStringContainsString('fails closed', $phase);
    }
}
