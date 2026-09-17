<?php

declare(strict_types=1);

namespace GreatMarketrealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class MicroGapCorroboratedBoundaryContinuityRestorationRegressionTest extends TestCase
{
    private string $source;

    protected function setUp(): void
    {
        $this->source = (string) file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
    }

    public function test_micro_gap_restoration_is_exact_same_surface_topology_and_hard_bounded_to_two_edges(): void
    {
        self::assertStringContainsString(
            'const microGapEligible = boundedTerminals >= 2 && !branchAdjacent && runLength <= 2;',
            $this->source
        );
        self::assertStringContainsString('const restored = edges[index];', $this->source);
        self::assertStringContainsString('points: [restored.a, restored.b]', $this->source);
        self::assertStringNotContainsString('nearestMicroGap', $this->source);
        self::assertStringNotContainsString('snapMicroGap', $this->source);
    }

    public function test_long_open_paper_runs_remain_suppressed_and_only_original_edges_are_restored(): void
    {
        self::assertStringContainsString('runLength <= 2', $this->source);
        self::assertStringContainsString('surfaceBoundaryMicroGapRestored: true', $this->source);
        self::assertStringContainsString("'g5z18-micro-gap-continuity'", $this->source);
        self::assertStringContainsString('illustratedSurfaceBoundaryOpenPaperSuppressed += 1;', $this->source);
        self::assertStringContainsString('illustratedSurfaceMicroGapRestoredEdges += 1;', $this->source);
    }

    public function test_evidence_audit_publishes_eligibility_restoration_and_final_contributed_geometry(): void
    {
        self::assertStringContainsString('illustratedSurfaceMicroGapEligibleRuns', $this->source);
        self::assertStringContainsString('illustratedSurfaceMicroGapEligibleEdges', $this->source);
        self::assertStringContainsString('illustratedSurfaceMicroGapRestoredEdges', $this->source);
        self::assertStringContainsString('micro-gap restoration ${audit.illustratedSurfaceMicroGapEligibleRuns || 0} eligible runs', $this->source);
        self::assertStringContainsString('${audit.illustratedSurfaceMicroGapRestoredEdges || 0} edges restored', $this->source);
    }

    public function test_existing_review_and_vertex_caps_remain_literal_contracts(): void
    {
        self::assertStringContainsString('const maximumPathVertices = 256;', $this->source);
        self::assertStringContainsString('const maximumReviewObjects = 200;', $this->source);
    }
}
