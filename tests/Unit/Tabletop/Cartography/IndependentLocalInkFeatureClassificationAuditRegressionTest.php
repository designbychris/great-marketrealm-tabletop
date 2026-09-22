<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class IndependentLocalInkFeatureClassificationAuditRegressionTest extends TestCase
{
    public function test_g5z35_preserves_uncertainty_and_never_promotes_short_ink_to_wall(): void
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertIsString($source);
        self::assertStringContainsString('const localInkFeatureClassification = localStructuralContext.map((context, index) => {', $source);
        self::assertStringContainsString('nearbyParallel, nearbyPerpendicular', $source);
        self::assertStringContainsString('isolated-short-ink-feature-review-not-wall-certified', $source);
        self::assertStringContainsString('orientation-and-neighbourhood-only-no-semantic-wall-certification', $source);
        self::assertStringContainsString('independentLocalInkFeatureClassificationAudit: {', $source);
        self::assertStringContainsString('G.5Z.35 ink features ·', $source);
        self::assertStringContainsString('dataset.cartographyInkFeatures', $source);
        self::assertStringContainsString('wallCertified: false, admittedEdges: 0, restoredRuns: 0', $source);
    }
}
