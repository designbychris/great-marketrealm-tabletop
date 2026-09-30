<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class WallKnowsWhatLiesBeyondRegressionTest extends TestCase
{
    public function test_g5z50f_audits_exact_normal_context_persistence_at_three_distances(): void
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertIsString($source);

        self::assertStringContainsString('IV.30.1G.5Z.50F — The Wall Knows What Lies Beyond.', $source);
        self::assertStringContainsString('const barrierBandSideCellAtDistance=', $source);
        self::assertStringContainsString("const sideProfile=(side)=>[1,2,3].map", $source);
        self::assertStringContainsString("'persistent-playable-floor-both-sides'", $source);
        self::assertStringContainsString("'persistent-playable-floor-to-hatch-exterior'", $source);
        self::assertStringContainsString("'persistent-playable-floor-to-open-paper'", $source);
        self::assertStringContainsString("'transition-decays-or-mixes'", $source);
        self::assertStringContainsString('illustratedPropagationBarrierBeyondClassifications', $source);
        self::assertStringContainsString('G.5Z.50F wall knows what lies beyond', $source);
        self::assertStringContainsString('exact-normal-1-2-3-cell-context-persistence-not-wall-certification;no-recovery-replay;G.5Z.50-veto-unchanged', $source);
    }

    public function test_g5z50f_is_diagnostic_only_and_preserves_frozen_cartography_contracts(): void
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertIsString($source);

        self::assertStringContainsString('if (propagationBarrier.corroborated) {', $source);
        self::assertStringContainsString('illustratedPropagationBarrierVetoes+=1;', $source);
        self::assertStringContainsString('const reconstructedSurfaceReviewCeiling = maximumReviewSuggestions;', $source);
        self::assertStringContainsString('const maximumPathVertices = 256;', $source);
        self::assertStringContainsString('Diagnostic marks are never saved.', $source);
        self::assertStringContainsString('no nearest-wall search, snapping, bridging, recovery replay', $source);
    }
}
