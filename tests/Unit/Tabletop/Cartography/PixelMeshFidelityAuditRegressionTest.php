<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class PixelMeshFidelityAuditRegressionTest extends TestCase
{
    public function test_g5z43_exposes_bounded_neighbourhood_diagnostics_without_admitting_walls(): void
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertIsString($source);
        self::assertStringContainsString('IV.30.1G.5Z.43 — Pixel-to-Mesh Fidelity Audit', $source);
        self::assertStringContainsString('const pixelMeshFidelityAudit = (() => {', $source);
        self::assertStringContainsString('neighbourhoodDark', $source);
        self::assertStringContainsString('pixelMeshFidelityAudit: pixelMeshFidelityAudit,', $source);
        self::assertStringContainsString('dataset.cartographyPixelMeshFidelity', $source);
        self::assertStringContainsString('G.5Z.43 pixel-to-mesh fidelity unavailable', $source);
        self::assertStringContainsString('wallCertified: false, admittedEdges: 0, restoredRuns: 0', $source);
    }
}
