<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class IllustratedBoundaryCorrespondenceAuditRegressionTest extends TestCase
{
    public function test_g5z40_samples_original_ink_without_promoting_walls(): void
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertIsString($source);
        self::assertStringContainsString('IV.30.1G.5Z.40 — Illustrated Boundary Correspondence Audit', $source);
        self::assertStringContainsString('authoritativeContourSuggestions.concat(promotedReconstructedSurfaceSuggestions)', $source);
        self::assertStringContainsString('const offsets = [0, contourStep * .5, contourStep, contourStep * 1.5]', $source);
        self::assertStringContainsString('sampleBudget = 120000', $source);
        self::assertStringContainsString('illustratedBoundaryCorrespondenceAudit: illustratedBoundaryCorrespondence,', $source);
        self::assertStringContainsString('dataset.cartographyIllustratedCorrespondence', $source);
        self::assertStringContainsString('unrepresented-walls-not-enumerated', $source);
        self::assertStringContainsString('wallCertified: false, admittedEdges: 0, restoredRuns: 0', $source);
    }
}
