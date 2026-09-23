<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class IndependentWallCoverageAuditRegressionTest extends TestCase
{
    public function test_g5z38_surveys_original_ink_without_promoting_candidate_runs_to_walls(): void
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertIsString($source);
        self::assertStringContainsString('IV.30.1G.5Z.38 — Independently inspect directional ink runs', $source);
        self::assertStringContainsString("['horizontal', 1, 0], ['vertical', 0, 1]", $source);
        self::assertStringContainsString('Number(darkness[y+length*dy]?.[x+length*dx] ?? 0)', $source);
        self::assertStringContainsString('authoritativeContourSuggestions.forEach(collect)', $source);
        self::assertStringContainsString('promotedReconstructedSurfaceSuggestions.forEach(collect)', $source);
        self::assertStringContainsString("'independent-original-ink-directional-run-screen-not-wall-certification'", $source);
        self::assertStringContainsString('directional-ink-can-be-grid-hatching-decoration-or-wall;not-exhaustive-illustration-coverage', $source);
        self::assertStringContainsString('cartographyEvidenceAudit.independentWallCoverageAudit = independentWallCoverage', $source);
        self::assertStringContainsString('dataset.cartographyIndependentCoverage', $source);
        self::assertStringContainsString('wholeIllustrationCoverageCertified: false, missingWallsCertified: false,', $source);
        self::assertStringContainsString('wallCertified: false, admittedEdges: 0, restoredRuns: 0', $source);
    }
}
