<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class ResidualCycleGeometryExteriorSeparationAuditRegressionTest extends TestCase
{
    public function test_g5z28_reports_geometry_and_exterior_evidence_without_restoring_suppressed_edges(): void
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertIsString($source);
        self::assertStringContainsString('const residualCycleGeometryAudit = (() => {', $source);
        self::assertStringContainsString('const cycleCandidate = residualClosureAudit.graphClosureCandidates > 0;', $source);
        self::assertStringContainsString('duplicateEdges === 0 && branchVertices === 0 && interiorCrossings === 0', $source);
        self::assertStringContainsString("'suppressed-open-paper-frontier'", $source);
        self::assertStringContainsString('geometryCertified: false, exteriorSeparationCertified: false,', $source);
        self::assertStringContainsString('admittedEdges: 0, restoredRuns: 0', $source);
        self::assertStringContainsString('                    residualCycleGeometryAudit,', $source);
        self::assertStringContainsString('G.5Z.28 geometry ·', $source);
        self::assertStringContainsString('cartographyAuditRuntimeWitness.dataset.cartographyGeometry', $source);
    }
}
