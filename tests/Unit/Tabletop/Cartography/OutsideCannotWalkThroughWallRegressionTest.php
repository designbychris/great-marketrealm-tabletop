<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class OutsideCannotWalkThroughWallRegressionTest extends TestCase
{
    public function test_g5z50q_compares_bounded_local_topology_with_the_fixed_route_blocked_and_open(): void
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertIsString($source);
        self::assertStringContainsString('IV.30.1G.5Z.50Q — The Outside Cannot Walk Through the Wall.', $source);
        self::assertStringContainsString('const barrierTopologyConnectivity=', $source);
        self::assertStringContainsString('const barrierOutsideTopologyReviews=', $source);
        self::assertStringContainsString('local-bypass-around-fixed-route-review', $source);
        self::assertStringContainsString('connection-requires-fixed-route-corridor-review', $source);
        self::assertStringContainsString('no-local-playable-connection-review', $source);
        self::assertStringContainsString('G.5Z.50Q outside cannot walk through the wall', $source);
        self::assertStringContainsString('bounded-exact-four-neighbour-recovered-topology;fixed-route-corridor-blocked-versus-open;three-cell-local-envelope;no-global-flood;no-route-shift;no-nearest-wall-search;no-recovery-replay;G.5Z.50-veto-unchanged', $source);
        self::assertStringContainsString('const maximumReviewSuggestions = 200;', $source);
        self::assertStringContainsString('const reconstructedSurfaceReviewCeiling = maximumReviewSuggestions;', $source);
        self::assertStringContainsString('const maximumPathVertices = 256;', $source);
    }
}
