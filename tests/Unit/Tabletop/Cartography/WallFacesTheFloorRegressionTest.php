<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class WallFacesTheFloorRegressionTest extends TestCase
{
    public function test_g5z50m_compares_fixed_route_side_with_persistent_floor_side_without_changing_production_barrier(): void
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertIsString($source);
        self::assertStringContainsString('IV.30.1G.5Z.50M — The Wall Faces the Floor.', $source);
        self::assertStringContainsString('const barrierFloorFacingReviews=', $source);
        self::assertStringContainsString("routeSide===-floorSide", $source);
        self::assertStringContainsString('floor-reaches-opposite-side-route-review', $source);
        self::assertStringContainsString('opposite-side-route-without-room-reach-review', $source);
        self::assertStringContainsString('route-lies-on-floor-side-review', $source);
        self::assertStringContainsString('floor-both-sides-facing-control-review', $source);
        self::assertStringContainsString('G.5Z.50M wall faces the floor', $source);
        self::assertStringContainsString('fixed-route-side-versus-persistent-floor-side-not-wall-certification;no-route-shift;no-nearest-wall-search;no-recovery-replay;G.5Z.50-veto-unchanged', $source);
        self::assertStringContainsString('const maximumReviewSuggestions = 200;', $source);
        self::assertStringContainsString('const reconstructedSurfaceReviewCeiling = maximumReviewSuggestions;', $source);
        self::assertStringContainsString('const maximumPathVertices = 256;', $source);
    }
}
