<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class RepresentedGeometryVisualCorrespondenceAuditRegressionTest extends TestCase
{
    public function test_represented_surface_geometry_publishes_spatial_correspondence_accounting(): void
    {
        $script = file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertIsString($script);

        self::assertStringContainsString('IV.30.1G.5Z.3 — Represented Geometry Visual Correspondence Audit.', $script);
        self::assertStringContainsString('reconstructedSurfaceSourceSegmentKeys', $script);
        self::assertStringContainsString('reconstructedSurfaceFinalSegmentKeys', $script);
        self::assertStringContainsString('reconstructedSurfaceRenderedReviewSegments', $script);
        self::assertStringContainsString('reconstructedSurfaceExactCorrespondenceEdges', $script);
        self::assertStringContainsString('reconstructedSurfaceAuthorityCorrespondenceEdges', $script);
        self::assertStringContainsString('reconstructedSurfaceNovelCorrespondenceEdges', $script);
        self::assertStringContainsString('reconstructedSurfaceDisplacedEdges', $script);
        self::assertStringContainsString('reconstructedSurfaceCollapsedEdges', $script);
        self::assertStringContainsString('reconstructedSurfaceOpenChainTerminations:', $script);
        self::assertStringContainsString('exact-correspondence edges', $script);
        self::assertStringContainsString('displaced edges', $script);
        self::assertStringContainsString('collapsed edges', $script);
        self::assertStringContainsString('open-chain terminations', $script);
    }
}
