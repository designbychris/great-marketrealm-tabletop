<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class OutsideMustStayOutsideRegressionTest extends TestCase
{
    public function test_g5z50o_checks_only_bounded_tangent_persistence_without_changing_production_barrier(): void
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertIsString($source);
        self::assertStringContainsString('IV.30.1G.5Z.50O — The Outside Must Stay Outside.', $source);
        self::assertStringContainsString('const barrierOutsideTangentStations=[-1,0,1];', $source);
        self::assertStringContainsString('const barrierOutsidePersistenceReviews=', $source);
        self::assertStringContainsString('persistent-exterior-along-route-review', $source);
        self::assertStringContainsString('exterior-decays-to-floor-or-mixed-review', $source);
        self::assertStringContainsString('persistent-floor-control-review', $source);
        self::assertStringContainsString('G.5Z.50O outside must stay outside', $source);
        self::assertStringContainsString('fixed-route-local-tangent-outside-persistence-not-wall-certification;fixed-tangent-stations-minus1-0-plus1;fixed-distances-0.25-0.5-1-2-3;no-route-shift;no-nearest-wall-search;no-recovery-replay;G.5Z.50-veto-unchanged', $source);
        self::assertStringContainsString('const maximumReviewSuggestions = 200;', $source);
        self::assertStringContainsString('const reconstructedSurfaceReviewCeiling = maximumReviewSuggestions;', $source);
        self::assertStringContainsString('const maximumPathVertices = 256;', $source);
    }
}
