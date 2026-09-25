<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class AdaptiveInkSamplingAuditRegressionTest extends TestCase
{
    public function test_g5z45_compares_bounded_adaptive_sampling_without_wall_admission(): void
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertIsString($source);
        self::assertStringContainsString('IV.30.1G.5Z.45 — Adaptive Ink Sampling Audit', $source);
        self::assertStringContainsString('const adaptiveInkSamplingAudit = (() => {', $source);
        self::assertStringContainsString('const fullCellCandidate = fullDensity >= threshold;', $source);
        self::assertStringContainsString('const insetCandidate = insetDensity >= threshold;', $source);
        self::assertStringContainsString('horizontalPairs', $source);
        self::assertStringContainsString('verticalPairs', $source);
        self::assertStringContainsString('isolatedDark', $source);
        self::assertStringContainsString('adaptiveInkSamplingAudit: adaptiveInkSamplingAudit,', $source);
        self::assertStringContainsString('dataset.cartographyAdaptiveInkSampling', $source);
        self::assertStringContainsString('G.5Z.45 adaptive ink sampling unavailable', $source);
        self::assertStringContainsString('wallCertified: false, admittedEdges: 0, restoredRuns: 0', $source);
    }
}
