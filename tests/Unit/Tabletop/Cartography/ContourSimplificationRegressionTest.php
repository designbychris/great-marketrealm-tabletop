<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class ContourSimplificationRegressionTest extends TestCase
{
    private function root(string $path): string
    {
        return dirname(__DIR__, 4) . '/' . $path;
    }

    public function test_living_contour_traces_complete_connected_chains_and_cycles(): void
    {
        $js = (string) file_get_contents($this->root('assets/js/tabletop.js'));
        self::assertStringContainsString('edgeAdjacency', $js);
        self::assertStringContainsString('traceChain', $js);
        self::assertStringContainsString('contourChains', $js);
        self::assertStringContainsString('closed contour cycles', $js);
    }

    public function test_complete_paths_are_simplified_before_review_budgeting(): void
    {
        $js = (string) file_get_contents($this->root('assets/js/tabletop.js'));
        self::assertStringContainsString('simplifyOpenPath', $js);
        self::assertStringContainsString('simplifyContourPath', $js);
        self::assertStringContainsString('buildSimplifiedSuggestions', $js);
        self::assertStringContainsString('pointLineDistance', $js);
    }

    public function test_review_budget_uses_adaptive_simplification_instead_of_scan_order_truncation(): void
    {
        $js = (string) file_get_contents($this->root('assets/js/tabletop.js'));
        self::assertStringContainsString('maximumReviewSuggestions = 200', $js);
        self::assertStringContainsString('simplificationTolerance *= 1.35', $js);
        self::assertStringContainsString('if (simplifiedValues.length > maximumReviewSuggestions) return []', $js);

        $contourBranchStart = strpos($js, "if (detail === 'contour')");
        $structuralBranchStart = strpos($js, "if (detail === 'structural')", $contourBranchStart ?: 0);
        self::assertNotFalse($contourBranchStart);
        self::assertNotFalse($structuralBranchStart);
        $contourBranch = substr($js, (int) $contourBranchStart, (int) $structuralBranchStart - (int) $contourBranchStart);
        self::assertStringNotContainsString('.slice(0, 200)', $contourBranch);
    }

    public function test_full_boundary_output_remains_review_first_and_fractional(): void
    {
        $js = (string) file_get_contents($this->root('assets/js/tabletop.js'));
        self::assertStringContainsString('fullBoundary: true', $js);
        self::assertStringContainsString('roundContourCoordinate', $js);
        self::assertStringContainsString('renderCartographyReview()', $js);
        self::assertStringContainsString('Nothing is saved until Apply Selected', $js);
    }

    public function test_roadmap_records_full_boundary_phase_without_absorbing_grid_registration(): void
    {
        $roadmap = (string) file_get_contents($this->root('ROADMAP.md'));
        $phase = (string) file_get_contents($this->root('docs/Roadmap/PHASE-IV.30.1B.2.md'));
        self::assertStringContainsString('[x] **IV.30.1B.2 — Contour Simplification & Full-Boundary Tracing**', $roadmap);
        self::assertStringContainsString('Advanced — cave dungeon', $phase);
        self::assertStringContainsString('misaligned-grid', $phase);
        self::assertStringContainsString('trace the complete boundary first, simplify it second, enforce the review budget last', $phase);
    }
}
