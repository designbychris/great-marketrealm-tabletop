<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class SuppressedBoundaryRunSpanInteriorEvidenceAuditRegressionTest extends TestCase
{
    private function source(): string
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertIsString($source);
        return $source;
    }

    public function test_g5z19_profiles_long_suppressed_runs_without_restoring_them(): void
    {
        $source = $this->source();
        self::assertStringContainsString('IV.30.1G.5Z.19 — Suppressed Boundary Run Span & Interior Evidence Audit.', $source);
        self::assertStringContainsString('runLength > 2', $source);
        self::assertStringContainsString('illustratedSurfaceLongGapExactLengths.push(runLength);', $source);
        self::assertStringContainsString('illustratedSurfaceLongGapNearCutoffEdges', $source);
        self::assertStringContainsString('illustratedSurfaceLongGapDeepQuietEdges', $source);
        self::assertStringContainsString('illustratedSurfaceLongGapInteriorDepthSupportedEdges', $source);
        self::assertStringContainsString('illustratedSurfaceLongGapLargestSpanCells', $source);
        self::assertStringContainsString('long-run forensic ${audit.illustratedSurfaceLongGapRuns || 0} runs', $source);
    }

    public function test_g5z19_preserves_g5z18_micro_gap_and_review_limits(): void
    {
        $source = $this->source();
        self::assertStringContainsString('const microGapEligible = boundedTerminals >= 2 && !branchAdjacent && runLength <= 2;', $source);
        self::assertStringContainsString('const maximumReviewSuggestions = 200;', $source);
        self::assertStringContainsString('const maximumPathVertices = 256;', $source);
        self::assertStringContainsString('Diagnostic marks are never saved.', $source);
    }
}
