<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class ResidualInkReviewDispositionAuditRegressionTest extends TestCase
{
    public function test_g5z36_closes_only_correlated_local_reviews_without_certifying_walls(): void
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertIsString($source);
        self::assertStringContainsString('const residualInkDisposition = review.map((component, index) => {', $source);
        self::assertStringContainsString('JSON.stringify(record.bounds) === JSON.stringify(component.bounds)', $source);
        self::assertStringContainsString('connectedReviewPixels === component.unrepresentedSamples', $source);
        self::assertStringContainsString('local-review-complete-isolated-ink-not-wall-certified', $source);
        self::assertStringContainsString('local-review-open-insufficient-evidence', $source);
        self::assertStringContainsString('reopenOnNewIndependentEvidence: true', $source);
        self::assertStringContainsString("scope: 'local-ink-review-only-not-whole-illustration'", $source);
        self::assertStringContainsString('wholeIllustrationCoverageCertified: false, missingWallsCertified: false', $source);
        self::assertStringContainsString('residualInkReviewDispositionAudit: independentIllustratedWallSurvey.residualInkClosure', $source);
        self::assertStringContainsString('G.5Z.36 ink disposition ·', $source);
        self::assertStringContainsString('dataset.cartographyInkDisposition', $source);
        self::assertStringContainsString('wallCertified: false, admittedEdges: 0, restoredRuns: 0', $source);
    }
}
