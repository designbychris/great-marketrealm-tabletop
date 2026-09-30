<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class WallMustHaveSomewhereToStandRegressionTest extends TestCase
{
    public function test_g5z50g_compares_persistent_floor_open_candidates_with_floor_floor_controls(): void
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertIsString($source);

        self::assertStringContainsString('IV.30.1G.5Z.50G — The Wall Must Have Somewhere to Stand.', $source);
        self::assertStringContainsString('const barrierSpineMemberEvidence=', $source);
        self::assertStringContainsString("'persistent-playable-floor-to-open-paper'", $source);
        self::assertStringContainsString("'persistent-playable-floor-both-sides'", $source);
        self::assertStringContainsString("'candidate-floor-open'", $source);
        self::assertStringContainsString("'control-floor-both'", $source);
        self::assertStringContainsString("'strong-longitudinal-spine-review'", $source);
        self::assertStringContainsString('illustratedPropagationBarrierSpineRoles', $source);
        self::assertStringContainsString('G.5Z.50G wall must have somewhere to stand', $source);
    }

    public function test_g5z50g_is_exact_interface_diagnostic_only_and_preserves_frozen_contracts(): void
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertIsString($source);

        self::assertStringContainsString('exact-interface-bounded-tangent-spine-not-wall-certification;no-recovery-replay;G.5Z.50-veto-unchanged', $source);
        self::assertStringContainsString('if (propagationBarrier.corroborated) {', $source);
        self::assertStringContainsString('illustratedPropagationBarrierVetoes+=1;', $source);
        self::assertStringContainsString('const reconstructedSurfaceReviewCeiling = maximumReviewSuggestions;', $source);
        self::assertStringContainsString('const maximumPathVertices = 256;', $source);
        self::assertStringContainsString('Diagnostic marks are never saved.', $source);
        self::assertStringContainsString('no nearest-wall search, snapping, bridging, recovery replay', $source);
    }
}
