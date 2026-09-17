<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class SuppressedRunGeometricTravelEndpointDisplacementAuditRegressionTest extends TestCase
{
    private function source(): string
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertIsString($source);
        return $source;
    }

    public function test_g5z20_measures_exact_source_travel_and_true_terminal_displacement(): void
    {
        $source = $this->source();
        self::assertStringContainsString('IV.30.1G.5Z.20 — Suppressed Run Geometric Travel & Endpoint Displacement Audit.', $source);
        self::assertStringContainsString('const travelCells = runEdges.reduce', $source);
        self::assertStringContainsString('const terminalPoints = terminalEndpoints.map', $source);
        self::assertStringContainsString('endpointDisplacementCells', $source);
        self::assertStringContainsString('travelDisplacementPermille', $source);
        self::assertStringContainsString('illustratedSurfaceLongGapGeometry.push({', $source);
        self::assertStringContainsString('geometric travel [${(audit.illustratedSurfaceLongGapGeometry || [])', $source);
    }

    public function test_g5z20_is_diagnostic_only_and_preserves_certified_limits(): void
    {
        $source = $this->source();
        self::assertStringContainsString('const microGapEligible = boundedTerminals >= 2 && !branchAdjacent && runLength <= 2;', $source);
        self::assertStringContainsString('const maximumReviewSuggestions = 200;', $source);
        self::assertStringContainsString('const maximumPathVertices = 256;', $source);
        self::assertStringContainsString('No endpoint', $source);
        self::assertStringContainsString('Diagnostic marks are never saved.', $source);
    }
}
