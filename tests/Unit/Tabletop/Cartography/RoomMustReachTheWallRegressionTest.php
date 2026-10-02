<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class RoomMustReachTheWallRegressionTest extends TestCase
{
    public function test_g5z50l_audits_fixed_interface_to_fixed_route_strip_without_changing_production_barrier(): void
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertIsString($source);
        self::assertStringContainsString('IV.30.1G.5Z.50L — The Room Must Reach the Wall.', $source);
        self::assertStringContainsString('const barrierReachBridgeFractions=[.25,.50,.75,1];', $source);
        self::assertStringContainsString('const barrierReachBridgeSample=', $source);
        self::assertStringContainsString('continuous-playable-to-route-review', $source);
        self::assertStringContainsString('playable-terminates-before-route-review', $source);
        self::assertStringContainsString('ink-band-occupies-bridge-review', $source);
        self::assertStringContainsString('open-paper-gap-before-route-review', $source);
        self::assertStringContainsString('floor-both-sides-room-reach-control-review', $source);
        self::assertStringContainsString('G.5Z.50L room must reach the wall', $source);
        self::assertStringContainsString('fixed-interface-to-fixed-route-strip-not-wall-certification;fixed-fraction-sampling;no-route-shift;no-nearest-wall-search;no-recovery-replay;G.5Z.50-veto-unchanged', $source);
        self::assertStringContainsString('const maximumReviewSuggestions = 200;', $source);
        self::assertStringContainsString('const reconstructedSurfaceReviewCeiling = maximumReviewSuggestions;', $source);
        self::assertStringContainsString('const maximumPathVertices = 256;', $source);
    }
}
