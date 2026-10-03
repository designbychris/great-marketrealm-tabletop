<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class WallMustHaveAnOutsideRegressionTest extends TestCase
{
    public function test_g5z50n_samples_only_outward_from_the_fixed_route_without_changing_production_barrier(): void
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertIsString($source);
        self::assertStringContainsString('IV.30.1G.5Z.50N — The Wall Must Have an Outside.', $source);
        self::assertStringContainsString('const barrierOutsideDistances=[.25,.5,1,2,3];', $source);
        self::assertStringContainsString('const barrierOutsideSample=', $source);
        self::assertStringContainsString('routeOffset+outwardSide*distance', $source);
        self::assertStringContainsString('floor-wall-exterior-profile-review', $source);
        self::assertStringContainsString('floor-wall-floor-control-profile-review', $source);
        self::assertStringContainsString('floor-wall-mixed-profile-review', $source);
        self::assertStringContainsString('G.5Z.50N wall must have an outside', $source);
        self::assertStringContainsString('fixed-route-outward-normal-profile-not-wall-certification;fixed-distances-0.25-0.5-1-2-3;no-route-shift;no-nearest-wall-search;no-recovery-replay;G.5Z.50-veto-unchanged', $source);
        self::assertStringContainsString('const maximumReviewSuggestions = 200;', $source);
        self::assertStringContainsString('const reconstructedSurfaceReviewCeiling = maximumReviewSuggestions;', $source);
        self::assertStringContainsString('const maximumPathVertices = 256;', $source);
    }
}
