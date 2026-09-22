<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class IllustratedWallTerminationStructuralBoundaryConfidenceAuditRegressionTest extends TestCase
{
    public function test_g5z30_reports_each_retained_endpoint_separately_from_quiet_frontier_without_wall_admission(): void
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertIsString($source);
        self::assertStringContainsString('const residualIllustratedTerminationAudit = (() => {', $source);
        self::assertStringContainsString('promotedReconstructedSurfaceSuggestions[record.pathIndex - 1]', $source);
        self::assertStringContainsString('residualSuppressedByEndpoint.get(residualTerminationPointKey(record.point))', $source);
        self::assertStringContainsString("'retained-path-meets-quiet-frontier'", $source);
        self::assertStringContainsString("'mixed-local-evidence-review-required'", $source);
        self::assertStringContainsString('illustratedWallTerminationCertified: false, admittedEdges: 0, restoredRuns: 0', $source);
        self::assertStringContainsString('                    residualIllustratedTerminationAudit,', $source);
        self::assertStringContainsString('G.5Z.30 termination evidence ·', $source);
        self::assertStringContainsString('cartographyAuditRuntimeWitness.dataset.cartographyTerminationEvidence', $source);
    }
}
