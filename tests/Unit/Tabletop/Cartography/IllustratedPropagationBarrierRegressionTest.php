<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class IllustratedPropagationBarrierRegressionTest extends TestCase
{
    public function test_g5z50_uses_local_corroborated_interface_evidence_before_vetoing_recovery(): void
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertIsString($source);
        self::assertStringContainsString("IV.30.1G.5Z.50 — The Illustrated Propagation Barrier", $source);
        self::assertStringContainsString('const propagationBarrierEvidence = (parentColumn,parentRow,column,row) => {', $source);
        self::assertStringContainsString('const candidate=travelDensity>=4/9;', $source);
        self::assertStringContainsString('const corroborated=candidate&&boundaryDensity>=5/9&&parallelDensity>=4/9;', $source);
        self::assertStringContainsString('if (propagationBarrier.corroborated) {', $source);
        self::assertStringContainsString('illustratedPropagationBarrierVetoes+=1;', $source);
        self::assertStringContainsString('continue;', $source);
    }

    public function test_g5z50_reports_barrier_pressure_without_changing_review_or_vertex_contracts(): void
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertIsString($source);
        self::assertStringContainsString('propagation barriers ${audit.illustratedPropagationBarrierCandidates || 0} candidates', $source);
        self::assertStringContainsString('const maximumPathVertices = 256;', $source);
        self::assertStringContainsString('const reconstructedSurfaceReviewCeiling=200;', $source);
    }
}
