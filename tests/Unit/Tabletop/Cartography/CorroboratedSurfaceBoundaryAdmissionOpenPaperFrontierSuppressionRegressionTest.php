<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class CorroboratedSurfaceBoundaryAdmissionOpenPaperFrontierSuppressionRegressionTest extends TestCase
{
    private string $script;

    protected function setUp(): void
    {
        parent::setUp();
        $this->script = (string) file_get_contents(__DIR__ . '/../../../../assets/js/tabletop.js');
    }

    public function test_only_certified_open_paper_frontier_is_suppressed_before_surface_promotion(): void
    {
        self::assertStringContainsString('IV.30.1G.5Z.16 — Corroborated Surface Boundary Admission & Open-Paper Frontier Suppression.', $this->script);
        self::assertStringContainsString('if (boundaryInk <= .30) {', $this->script);
        self::assertStringContainsString('illustratedSurfaceBoundaryOpenPaperSuppressed += 1;', $this->script);
        self::assertStringContainsString('return;', $this->script);
        self::assertStringContainsString('illustratedSurfacePerimeterEdges += 1;', $this->script);
        self::assertStringContainsString('reconstructedIllustratedSurfaceEdges.push({', $this->script);
    }

    public function test_ambiguous_unsupported_frontier_remains_admitted_and_no_replacement_search_is_added(): void
    {
        self::assertStringContainsString('illustratedSurfaceBoundaryUnsupportedFrontier += 1;', $this->script);
        self::assertStringContainsString("let surfaceBoundaryCorroboration = 'unsupported-frontier';", $this->script);
        self::assertStringContainsString('do not weaken recovery or search for', $this->script);
        self::assertStringNotContainsString('nearestPlayable', $this->script);
    }

    public function test_suppression_preserves_threshold_vertex_capacity_and_overlay_safeguards(): void
    {
        self::assertStringContainsString('if (contourThresholdMatch(a,b))', $this->script);
        self::assertStringContainsString('const maximumSecondarySeedGenerations=10;', $this->script);
        self::assertStringContainsString('const maximumPathVertices = 256;', $this->script);
        self::assertStringContainsString('certifiedSurfaceOverlays', $this->script);
        self::assertStringContainsString('illustratedSurfaceBoundaryOpenPaperSuppressed,', $this->script);
        self::assertStringContainsString('${audit.illustratedSurfaceBoundaryOpenPaperSuppressed || 0} open-paper frontier suppressed', $this->script);
    }
}
