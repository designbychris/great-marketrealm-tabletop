<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class IllustratedWallCoverageResidualStructuralEvidenceAuditRegressionTest extends TestCase
{
    public function test_g5z31_reports_sampled_boundary_coverage_without_promoting_quiet_frontier(): void
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertIsString($source);
        self::assertStringContainsString('const residualIllustratedWallCoverageAudit = (() => {', $source);
        self::assertStringContainsString('illustratedSurfaceBoundaryTopology.map((edge) => {', $source);
        self::assertStringContainsString('retainedKeys.has(edge.key)', $source);
        self::assertStringContainsString('authoritativeKeys.has(edge.key)', $source);
        self::assertStringContainsString("'unrepresented-quiet-surface-frontier-not-wall'", $source);
        self::assertStringContainsString("'unrepresented-corroborated-sample-review-required'", $source);
        self::assertStringContainsString('wholeIllustrationCoverageCertified: false, admittedEdges: 0, restoredRuns: 0', $source);
        self::assertStringContainsString('                    residualIllustratedWallCoverageAudit,', $source);
        self::assertStringContainsString('G.5Z.31 wall coverage ·', $source);
        self::assertStringContainsString('cartographyAuditRuntimeWitness.dataset.cartographyWallCoverage', $source);
    }
}
