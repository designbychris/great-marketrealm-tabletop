<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class CoincidentEndpointSourceProvenanceAuditRegressionTest extends TestCase
{
    public function test_g5z25_uses_original_edge_provenance_and_keeps_classification_diagnostic_only(): void
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertIsString($source);
        self::assertStringContainsString('const originalSurfaceComponentsByEdge = new Map()', $source);
        self::assertStringContainsString('illustratedSurfaceBoundaryTopology.forEach((edge)', $source);
        self::assertStringContainsString('completedEdgeKey(points[i - 1], points[i])', $source);
        self::assertStringContainsString('missingEdges, ambiguousEdges, componentIds, verifiedComponentId', $source);
        self::assertStringContainsString("'same-source-local-continuation-candidate'", $source);
        self::assertStringContainsString('provenance, verifiedSameComponent, sourceClassification,', $source);
        self::assertStringContainsString('G.5Z.25 provenance', $source);
        self::assertStringContainsString('const maximumReviewObjects = 200;', $source);
        self::assertLessThan(strpos($source, 'const residualCoincidentEndpointGroups = []'), strpos($source, 'const residualPathProvenance ='));
    }
}
