<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class CornerRightfulNeighbourRegressionTest extends TestCase
{
    public function test_rightful_neighbour_audit_inspects_existing_incident_edges_without_mutation(): void
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertIsString($source);
        self::assertStringContainsString("IV.30.1G.5Z.50Z.6 — The Corner's Rightful Neighbour.", $source);
        self::assertStringContainsString('barrierCornerMissingConnectionReviews.map((review)', $source);
        self::assertStringContainsString('completedSurfaceEdgeKeys.has(completedEdgeKey({x:nx,y:ny},end))', $source);
        self::assertStringContainsString('surfaceStructuralEdge(nx,ny,end.x,end.y)', $source);
        self::assertStringContainsString('illustratedPropagationBarrierCornerRightfulNeighbourReviews,', $source);
        self::assertStringContainsString('diagnostic-only;exact-incident-edge-provenance;no-snapping;no-wall-certification;no-geometry-mutation;G.5Z.50-veto-unchanged', $source);
    }
}
