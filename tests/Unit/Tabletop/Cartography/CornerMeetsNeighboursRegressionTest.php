<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class CornerMeetsNeighboursRegressionTest extends TestCase
{
    public function test_neighbour_audit_uses_exact_shared_vertices_without_wall_admission(): void
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertIsString($source);
        self::assertStringContainsString('IV.30.1G.5Z.50Z.4 — The Corner Must Meet Its Neighbours.', $source);
        self::assertStringContainsString('completedSurfaceEdgeKeys.has(completedEdgeKey(point,other))', $source);
        self::assertStringContainsString('surfaceStructuralEdge(point.x,point.y,other.x,other.y)', $source);
        self::assertStringContainsString('illustratedPropagationBarrierCornerNeighbourReviews,', $source);
        self::assertStringContainsString('illustratedPropagationBarrierCornerNeighbourInterfaces,', $source);
        self::assertStringContainsString('diagnostic-only;exact-shared-vertices;no-wall-certification;no-geometry-mutation;G.5Z.50-veto-unchanged', $source);
    }
}
