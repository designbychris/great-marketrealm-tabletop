<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class SurfacePerimeterToPathAssemblyLossAuditRegressionTest extends TestCase
{
    public function test_completed_surface_perimeter_publishes_pre_representation_assembly_loss_accounting(): void
    {
        $script = file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertIsString($script);

        self::assertStringContainsString('IV.30.1G.5Z.7 — Surface Perimeter-to-Path Assembly Loss Audit.', $script);
        self::assertStringContainsString('reconstructedSurfaceAssembledSegmentKeys', $script);
        self::assertStringContainsString('reconstructedSurfaceUnassembledEdges', $script);
        self::assertStringContainsString('reconstructedSurfaceUnassembledComponents', $script);
        self::assertStringContainsString('reconstructedSurfaceLargestUnassembledComponent', $script);
        self::assertStringContainsString('reconstructedSurfaceUnassembledBranchAdjacentEdges', $script);
        self::assertStringContainsString('reconstructedSurfaceVertexCapRiskComponents', $script);
        self::assertStringContainsString('reconstructedSurfaceVertexCapRiskEdges', $script);
        self::assertStringContainsString('unassembled perimeter edges', $script);
        self::assertStringContainsString('unassembled components (largest', $script);
        self::assertStringContainsString('branch-adjacent unassembled edges', $script);
        self::assertStringContainsString('vertex-cap-risk components', $script);
        self::assertStringContainsString('promoteInferredPerimeterChains(reconstructedIllustratedSurfaceEdges)', $script);
        self::assertStringContainsString('maximumPathVertices = 256', $script);
    }
}
