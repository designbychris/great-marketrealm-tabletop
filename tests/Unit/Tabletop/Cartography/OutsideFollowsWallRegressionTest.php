<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class OutsideFollowsWallRegressionTest extends TestCase
{
    public function test_g5z50p_rotates_only_the_diagnostic_probe_frame_with_the_fixed_route(): void
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertIsString($source);
        self::assertStringContainsString('IV.30.1G.5Z.50P — The Outside Follows the Wall.', $source);
        self::assertStringContainsString('const barrierRouteLocalFrame=', $source);
        self::assertStringContainsString('const barrierOutsideFollowingReviews=', $source);
        self::assertStringContainsString('route-turn-restores-exterior-review', $source);
        self::assertStringContainsString('route-local-exterior-still-decays-review', $source);
        self::assertStringContainsString('route-local-floor-control-confirmed-review', $source);
        self::assertStringContainsString('G.5Z.50P outside follows the wall', $source);
        self::assertStringContainsString('adjacent-fixed-route-points-only;outward-side-preserved;fixed-tangent-stations-minus1-0-plus1;fixed-distances-0.25-0.5-1-2-3;no-route-shift;no-nearest-wall-search;no-recovery-replay;G.5Z.50-veto-unchanged', $source);
        self::assertStringContainsString('const maximumReviewSuggestions = 200;', $source);
        self::assertStringContainsString('const reconstructedSurfaceReviewCeiling = maximumReviewSuggestions;', $source);
        self::assertStringContainsString('const maximumPathVertices = 256;', $source);
    }
}
