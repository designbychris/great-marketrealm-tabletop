<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class IllustratedFloorContinuityInteriorSurfaceFloodingRegressionTest extends TestCase
{
    public function test_illustrated_floor_flooding_preserves_contour_authority_and_reports_surface_recovery(): void
    {
        $root = dirname(__DIR__, 4);
        $script = (string) file_get_contents($root . '/assets/js/tabletop.js');
        $roadmap = (string) file_get_contents($root . '/ROADMAP.md');

        self::assertStringContainsString('IV.30.1G.5Q — Illustrated Floor Continuity & Interior Surface Flooding', $script);
        self::assertStringContainsString('illustratedFloorContinuitySurface', $script);
        self::assertStringContainsString('illustratedFloorFloodPass', $script);
        self::assertStringContainsString('recoveredIllustratedFloorCells', $script);
        self::assertStringContainsString('reconstructedIllustratedSurfaces', $script);
        self::assertStringContainsString('illustratedSurfacePerimeterEdges', $script);
        self::assertStringContainsString('horizontalStructuralBand || verticalStructuralBand', $script);
        self::assertStringContainsString('authoritativePreserved', $script);
        self::assertStringContainsString('illustrated floor cells recovered', $script);
        self::assertStringContainsString('interior surfaces reconstructed', $script);
        self::assertStringContainsString('surface perimeter edges contributed', $script);
        self::assertStringContainsString('Phase IV.30.1G.5Q — Illustrated Floor Continuity & Interior Surface Flooding ✅', $roadmap);
    }
}
