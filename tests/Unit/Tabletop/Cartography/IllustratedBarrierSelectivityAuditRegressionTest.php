<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class IllustratedBarrierSelectivityAuditRegressionTest extends TestCase
{
    public function test_g5z50a_classifies_exact_veto_interfaces_without_changing_the_g5z50_veto(): void
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertIsString($source);
        self::assertStringContainsString('IV.30.1G.5Z.50A — The Illustrated Barrier Selectivity Audit.', $source);
        self::assertStringContainsString('const illustratedPropagationBarrierSelectivityRecords = [];', $source);
        self::assertStringContainsString('const barrierVetoRecords=illustratedPropagationBarrierSelectivityRecords.filter((entry)=>entry.corroborated);', $source);
        self::assertStringContainsString("const barrierInterfaceKey=(entry)=>`${entry.orientation}:${entry.interfaceColumn}:${entry.interfaceRow}`;", $source);
        self::assertStringContainsString('if (propagationBarrier.corroborated) {', $source);
        self::assertStringContainsString('illustratedPropagationBarrierVetoes+=1;', $source);
        self::assertStringContainsString('continue;', $source);
    }

    public function test_g5z50a_publishes_selectivity_cohorts_and_preserves_frozen_geometry_contracts(): void
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertIsString($source);
        self::assertStringContainsString('G.5Z.50A selectivity ${audit.illustratedPropagationBarrierSelectivityCandidates || 0} candidate events', $source);
        self::assertStringContainsString('continuity isolated:${audit.illustratedPropagationBarrierSelectivityIsolated || 0}', $source);
        self::assertStringContainsString('pair-end:${audit.illustratedPropagationBarrierSelectivityPair || 0}', $source);
        self::assertStringContainsString('sustained:${audit.illustratedPropagationBarrierSelectivitySustained || 0}', $source);
        self::assertStringContainsString('turning/branch-adjacent:${audit.illustratedPropagationBarrierSelectivityTurning || 0}', $source);
        self::assertStringContainsString('dense-hatching:${audit.illustratedPropagationBarrierSelectivityDense || 0}', $source);
        self::assertStringContainsString('diagnostic-only;G.5Z.50-veto-unchanged', $source);
        self::assertStringContainsString('const maximumPathVertices = 256;', $source);
        self::assertStringContainsString('const reconstructedSurfaceReviewCeiling = maximumReviewSuggestions;', $source);
    }
}
