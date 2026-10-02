<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class WallIsWiderThanALineRegressionTest extends TestCase
{
    public function test_g5z50h_compares_fixed_width_routes_for_candidate_and_control_runs(): void
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertIsString($source);

        self::assertStringContainsString('IV.30.1G.5Z.50H — The Wall Is Wider Than a Line.', $source);
        self::assertStringContainsString('const barrierWidthTrackOffsets=[-.50,-.25,0,.25,.50];', $source);
        self::assertStringContainsString('const barrierWidthTangentOffsets=[-.20,0,.20];', $source);
        self::assertStringContainsString('const barrierWidthRoute=', $source);
        self::assertStringContainsString('Math.abs(previousIndex-trackIndex)<=1', $source);
        self::assertStringContainsString("'candidate-floor-open','control-floor-both'", $source);
        self::assertStringContainsString("'bounded-width-continuous-route-review'", $source);
        self::assertStringContainsString('illustratedPropagationBarrierWidthRoles', $source);
        self::assertStringContainsString('G.5Z.50H wall is wider than a line', $source);
    }

    public function test_g5z50h_is_bounded_diagnostic_only_and_preserves_frozen_contracts(): void
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertIsString($source);

        self::assertStringContainsString('fixed-five-track-bounded-width-route-not-wall-certification;track-drift-at-most-one;no-nearest-wall-search;no-recovery-replay;G.5Z.50-veto-unchanged', $source);
        self::assertStringContainsString('if (propagationBarrier.corroborated) {', $source);
        self::assertStringContainsString('illustratedPropagationBarrierVetoes+=1;', $source);
        self::assertStringContainsString('const reconstructedSurfaceReviewCeiling = maximumReviewSuggestions;', $source);
        self::assertStringContainsString('const maximumPathVertices = 256;', $source);
        self::assertStringContainsString('Diagnostic marks are never saved.', $source);
    }
}
