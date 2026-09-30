<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class SustainedBarrierAuditSpeaksRegressionTest extends TestCase
{
    public function test_g5z50d1_surfaces_the_existing_band_audit_before_the_longer_selectivity_diagnostics(): void
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertIsString($source);

        $speech = strpos($source, 'G.5Z.50D.1 sustained barrier audit speaks');
        $band = strpos($source, 'G.5Z.50D sustained barrier bands');
        $selectivity = strpos($source, 'G.5Z.50A selectivity');

        self::assertNotFalse($speech);
        self::assertNotFalse($band);
        self::assertNotFalse($selectivity);
        self::assertLessThan($band, $speech);
        self::assertLessThan($selectivity, $band);
        self::assertStringContainsString('illustratedPropagationBarrierBandEvidenceClassifications || []', $source);
        self::assertStringContainsString('illustratedPropagationBarrierBandEvidenceReviews || []', $source);
        self::assertStringContainsString('original-image-exact-run-band-review-not-wall-certification;no-recovery-replay;G.5Z.50-veto-unchanged', $source);
    }

    public function test_g5z50d1_is_reporting_only_and_keeps_frozen_cartography_contracts(): void
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertIsString($source);

        self::assertStringContainsString('if (propagationBarrier.corroborated) {', $source);
        self::assertStringContainsString('illustratedPropagationBarrierVetoes+=1;', $source);
        self::assertStringContainsString('const reconstructedSurfaceReviewCeiling = maximumReviewSuggestions;', $source);
        self::assertStringContainsString('const maximumPathVertices = 256;', $source);
        self::assertStringContainsString('Diagnostic marks are never saved.', $source);
    }
}
