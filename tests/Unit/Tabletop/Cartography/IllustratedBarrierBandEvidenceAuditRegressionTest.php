<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class IllustratedBarrierBandEvidenceAuditRegressionTest extends TestCase
{
    public function test_g5z50d_reviews_exact_sustained_runs_against_original_image_without_replaying_recovery(): void
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertIsString($source);

        self::assertStringContainsString('IV.30.1G.5Z.50D — The Sustained Barrier Band Evidence Audit.', $source);
        self::assertStringContainsString('barrierRunSummaries.filter((run)=>run.length>=3)', $source);
        self::assertStringContainsString("classification='continuous-band-review'", $source);
        self::assertStringContainsString("classification='intermittent-band-review'", $source);
        self::assertStringContainsString("classification='hatching-like-review'", $source);
        self::assertStringContainsString('longitudinalPermille:', $source);
        self::assertStringContainsString('orientationPermille:', $source);
        self::assertStringContainsString('thicknessVariationPermille:', $source);
        self::assertStringContainsString('illustratedPropagationBarrierBandEvidenceClassifications,', $source);
        self::assertStringContainsString('original-image-exact-run-band-review-not-wall-certification;no-recovery-replay;G.5Z.50-veto-unchanged', $source);
    }

    public function test_g5z50d_keeps_existing_barrier_and_frozen_limits(): void
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
