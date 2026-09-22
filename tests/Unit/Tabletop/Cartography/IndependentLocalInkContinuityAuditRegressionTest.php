<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class IndependentLocalInkContinuityAuditRegressionTest extends TestCase
{
    public function test_g5z34_reports_bounded_local_ink_without_promoting_geometry(): void
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertIsString($source);
        self::assertStringContainsString('const localStructuralContext = review.map((component) => {', $source);
        self::assertStringContainsString('const margin = 6;', $source);
        self::assertStringContainsString('const visitedInk = new Set();', $source);
        self::assertStringContainsString('touchesSurveyWindow:', $source);
        self::assertStringContainsString("classification: 'local-ink-continuity-structural-context-unverified', wallCertified: false", $source);
        self::assertStringContainsString('independentLocalInkContinuityAudit: {', $source);
        self::assertStringContainsString('G.5Z.34 local ink continuity ·', $source);
        self::assertStringContainsString('dataset.cartographyLocalInk', $source);
        self::assertStringContainsString('wallCertified: false, admittedEdges: 0, restoredRuns: 0', $source);
    }
}
