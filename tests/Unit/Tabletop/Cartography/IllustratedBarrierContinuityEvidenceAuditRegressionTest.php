<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class IllustratedBarrierContinuityEvidenceAuditRegressionTest extends TestCase
{
    public function test_g5z50c_correlates_exact_continuity_cohorts_without_changing_recovery(): void
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertIsString($source);

        self::assertStringContainsString('IV.30.1G.5Z.50C — The Illustrated Barrier Continuity Evidence Audit.', $source);
        self::assertStringContainsString("barrierEvidenceCohortSummary('isolated',barrierEvidenceIsolated)", $source);
        self::assertStringContainsString("barrierEvidenceCohortSummary('run2',barrierEvidenceRun2)", $source);
        self::assertStringContainsString("barrierEvidenceCohortSummary('run3+-straight',barrierEvidenceRun3Straight)", $source);
        self::assertStringContainsString("barrierEvidenceCohortSummary('run3+-or-exact-corner',barrierEvidenceRun3OrCorner)", $source);
        self::assertStringContainsString('parentAndChildFloorLike:', $source);
        self::assertStringContainsString('floorLikeTransition:', $source);
        self::assertStringContainsString('provisionalDecoration:', $source);
        self::assertStringContainsString('illustratedPropagationBarrierContinuityEvidenceCohorts,', $source);
        self::assertStringContainsString('cohort-correlation-not-wall-certification;no-recovery-replay;G.5Z.50-veto-unchanged', $source);
    }

    public function test_g5z50c_keeps_the_existing_g5z50_barrier_and_frozen_limits(): void
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
