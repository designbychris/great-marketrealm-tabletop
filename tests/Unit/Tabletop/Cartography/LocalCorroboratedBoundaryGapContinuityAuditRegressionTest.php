<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class LocalCorroboratedBoundaryGapContinuityAuditRegressionTest extends TestCase
{
    private string $script;

    protected function setUp(): void
    {
        parent::setUp();
        $this->script = (string) file_get_contents(__DIR__ . '/../../../../assets/js/tabletop.js');
    }

    public function test_suppressed_open_paper_edges_are_audited_without_restoration(): void
    {
        self::assertStringContainsString('IV.30.1G.5Z.17 — Local Corroborated Boundary Gap Continuity Audit.', $this->script);
        self::assertStringContainsString('suppressedOpenPaper: true', $this->script);
        self::assertStringContainsString('suppressedOpenPaper: false', $this->script);
        self::assertStringContainsString('does not feed any suppressed edge back into promotion', $this->script);
        self::assertStringContainsString('illustratedSurfaceBoundaryOpenPaperSuppressed += 1;', $this->script);
    }

    public function test_gap_runs_use_exact_endpoints_and_same_source_surface_only(): void
    {
        self::assertStringContainsString('const completedBoundaryEndpointKey = (point)', $this->script);
        self::assertStringContainsString('suppressedBySurface.forEach((edges, surfaceComponentId)', $this->script);
        self::assertStringContainsString('const retainedEndpoints = retainedEndpointsBySurface.get(surfaceComponentId) || new Set();', $this->script);
        self::assertStringContainsString('if (boundedTerminals >= 2 && !branchAdjacent) illustratedSurfaceSuppressedGapSameSourceContiguous += 1;', $this->script);
        self::assertStringNotContainsString('nearestPlayable', $this->script);
    }

    public function test_audit_publishes_boundedness_branching_and_length_buckets(): void
    {
        foreach ([
            'illustratedSurfaceSuppressedGapRuns',
            'illustratedSurfaceSuppressedGapBothBounded',
            'illustratedSurfaceSuppressedGapOneBounded',
            'illustratedSurfaceSuppressedGapUnbounded',
            'illustratedSurfaceSuppressedGapBranchAdjacent',
            'illustratedSurfaceSuppressedGapSameSourceContiguous',
            'illustratedSurfaceSuppressedGapLength1',
            'illustratedSurfaceSuppressedGapLength2',
            'illustratedSurfaceSuppressedGapLength3To4',
            'illustratedSurfaceSuppressedGapLength5To8',
            'illustratedSurfaceSuppressedGapLength9Plus',
        ] as $counter) {
            self::assertStringContainsString($counter, $this->script);
        }
        self::assertStringContainsString('gap continuity ${audit.illustratedSurfaceSuppressedGapRuns || 0} runs', $this->script);
    }

    public function test_existing_suppression_and_safeguards_remain_intact(): void
    {
        self::assertStringContainsString('if (boundaryInk <= .30) {', $this->script);
        self::assertStringContainsString('if (contourThresholdMatch(a,b))', $this->script);
        self::assertStringContainsString('const maximumSecondarySeedGenerations=10;', $this->script);
        self::assertStringContainsString('const maximumPathVertices = 256;', $this->script);
        self::assertStringContainsString('certifiedSurfaceOverlays', $this->script);
    }
}
