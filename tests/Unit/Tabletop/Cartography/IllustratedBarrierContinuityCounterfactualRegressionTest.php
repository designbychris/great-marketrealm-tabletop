<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class IllustratedBarrierContinuityCounterfactualRegressionTest extends TestCase
{
    public function test_g5z50b_builds_exact_bounded_continuity_cohorts_without_replaying_recovery(): void
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertIsString($source);
        self::assertStringContainsString('IV.30.1G.5Z.50B — The Illustrated Barrier Continuity Counterfactual.', $source);
        self::assertStringContainsString('const barrierSameOrientationRunLength=new Map();', $source);
        self::assertStringContainsString('const barrierExactCornerNeighbours=(entry)=>{', $source);
        self::assertStringContainsString('const illustratedPropagationBarrierCounterfactualRun2=barrierCounterfactualCount(2);', $source);
        self::assertStringContainsString('const illustratedPropagationBarrierCounterfactualRun3=barrierCounterfactualCount(3);', $source);
        self::assertStringContainsString('const illustratedPropagationBarrierCounterfactualRun4=barrierCounterfactualCount(4);', $source);
        self::assertStringContainsString('diagnostic-only;no-recovery-replay;G.5Z.50-veto-unchanged', $source);
    }

    public function test_g5z50b_publishes_run_histogram_and_preserves_frozen_contracts(): void
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertIsString($source);
        self::assertStringContainsString('G.5Z.50B continuity counterfactual ${audit.illustratedPropagationBarrierSelectivityVetoes || 0} current vetoes', $source);
        self::assertStringContainsString('run≥2:${audit.illustratedPropagationBarrierCounterfactualRun2 || 0}', $source);
        self::assertStringContainsString('run≥3:${audit.illustratedPropagationBarrierCounterfactualRun3 || 0}', $source);
        self::assertStringContainsString('run≥4:${audit.illustratedPropagationBarrierCounterfactualRun4 || 0}', $source);
        self::assertStringContainsString('run3-or-exact-corner:${audit.illustratedPropagationBarrierCounterfactualCornerCoherent || 0}', $source);
        self::assertStringContainsString('const maximumPathVertices = 256;', $source);
        self::assertStringContainsString('const reconstructedSurfaceReviewCeiling = maximumReviewSuggestions;', $source);
    }
}
