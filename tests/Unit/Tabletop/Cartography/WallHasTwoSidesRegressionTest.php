<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class WallHasTwoSidesRegressionTest extends TestCase
{
    public function test_g5z50e_audits_exact_two_sided_context_for_sustained_bands(): void
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertIsString($source);

        self::assertStringContainsString('IV.30.1G.5Z.50E — The Wall Has Two Sides.', $source);
        self::assertStringContainsString("const barrierBandFinalPlayable=", $source);
        self::assertStringContainsString("const barrierBandSideCell=", $source);
        self::assertStringContainsString("'playable-floor-both-sides'", $source);
        self::assertStringContainsString("'playable-floor-to-hatch-exterior'", $source);
        self::assertStringContainsString("'playable-floor-to-open-paper'", $source);
        self::assertStringContainsString("'hatch-exterior-both-sides'", $source);
        self::assertStringContainsString("'mixed-unresolved'", $source);
        self::assertStringContainsString('illustratedPropagationBarrierTwoSidesClassifications', $source);
        self::assertStringContainsString('G.5Z.50E wall has two sides', $source);
        self::assertStringContainsString('exact-cell-two-sided-context-not-wall-certification;no-recovery-replay;G.5Z.50-veto-unchanged', $source);
    }

    public function test_g5z50e_remains_diagnostic_and_preserves_frozen_cartography_contracts(): void
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
