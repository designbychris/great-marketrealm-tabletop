<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class DirectionalInkContextAuditRegressionTest extends TestCase
{
    public function test_g5z39_reconciles_original_ink_reviews_without_admitting_walls(): void
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertIsString($source);
        self::assertStringContainsString('IV.30.1G.5Z.39 — Reconcile the independent directional review', $source);
        self::assertStringContainsString('independentWallCoverage.runs.filter', $source);
        self::assertStringContainsString('independentIllustratedWallSurvey.localInkFeatureClassification', $source);
        self::assertStringContainsString('parallelInkSamples: parallel, perpendicularInkSamples: perpendicular,', $source);
        self::assertStringContainsString('localReviewOverlaps: overlaps,', $source);
        self::assertStringContainsString('directionalInkContextAudit: directionalInkContext,', $source);
        self::assertStringContainsString('dataset.cartographyDirectionalInkContext', $source);
        self::assertStringContainsString('local-ink-overlap-and-directional-continuity-do-not-prove-structural-wall-identity', $source);
        self::assertStringContainsString('wallCertified: false, admittedEdges: 0, restoredRuns: 0', $source);
    }
}
