<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class VertexCapSafePerimeterSegmentationContinuityPreservationRegressionTest extends TestCase
{
    private string $source;

    protected function setUp(): void
    {
        $this->source = (string) file_get_contents(__DIR__ . '/../../../../assets/js/tabletop.js');
    }

    public function test_oversized_perimeter_is_segmented_without_raising_the_vertex_cap(): void
    {
        self::assertStringContainsString('IV.30.1G.5Z.8 — Vertex-Cap-Safe Perimeter Segmentation & Continuity Preservation', $this->source);
        self::assertStringContainsString('const maximumPathVertices = 256;', $this->source);
        self::assertStringContainsString('const maximumEdgesPerPath = maximumPathVertices - 1;', $this->source);
        self::assertStringContainsString('Math.ceil(run.members.length / maximumEdgesPerPath)', $this->source);
        self::assertStringContainsString('const segmentMembers = run.members.slice(memberStart, memberEnd);', $this->source);
        self::assertStringContainsString('const segmentPoints = run.points.slice(memberStart, memberEnd + 1);', $this->source);
        self::assertStringContainsString("emissionContinuity: segmentCount > 1 ? 'vertex-cap-contiguous-segment' : 'connected-perimeter-chain'", $this->source);
        self::assertStringContainsString("'vertex-cap-safe-segmentation', 'shared-split-vertex-continuity'", $this->source);
        self::assertStringContainsString('vertexCapSharedStart: segmentCount > 1 && segmentIndex > 0', $this->source);
        self::assertStringContainsString('vertexCapSharedEnd: segmentCount > 1 && segmentIndex < segmentCount - 1', $this->source);
    }

    public function test_segmentation_remains_inside_existing_review_and_geometry_safeguards(): void
    {
        self::assertStringContainsString('item.points.length <= maximumPathVertices', $this->source);
        self::assertStringContainsString('maximumReviewSuggestions', $this->source);
        self::assertStringContainsString('reconstructedSurfaceUnassembledEdges', $this->source);
        self::assertStringContainsString('reconstructedSurfaceVertexCapSegmentedPathCount', $this->source);
        self::assertStringContainsString('reconstructedSurfaceVertexCapSegmentedEdges', $this->source);
        self::assertStringContainsString('reconstructedSurfaceVertexCapSplitVertices', $this->source);
    }
}
