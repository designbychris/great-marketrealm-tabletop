<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class WallMustFaceTheRoomRegressionTest extends TestCase
{
    public function test_g5z50j_audits_route_facing_without_changing_production_barrier(): void
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertIsString($source);
        self::assertStringContainsString('IV.30.1G.5Z.50J — The Wall Must Face the Room.', $source);
        self::assertStringContainsString('const barrierFacingInterfaceProfile=', $source);
        self::assertStringContainsString("[1,2,3].map((distance)=>barrierBandBeyondSample(entry,side,distance))", $source);
        self::assertStringContainsString("consistent-room-facing-route-review", $source);
        self::assertStringContainsString("floor-both-sides-route-control-review", $source);
        self::assertStringContainsString("mixed-or-decaying-facing-review", $source);
        self::assertStringContainsString("'candidate-floor-open','control-floor-both'", $source);
        self::assertStringContainsString('G.5Z.50J wall must face the room', $source);
        self::assertStringContainsString('exact-route-normal-context-facing-not-wall-certification;no-side-flips;no-nearest-wall-search;no-recovery-replay;G.5Z.50-veto-unchanged', $source);
        self::assertStringContainsString('const maximumReviewSuggestions = 200;', $source);
        self::assertStringContainsString('const reconstructedSurfaceReviewCeiling = maximumReviewSuggestions;', $source);
        self::assertStringContainsString('const maximumPathVertices = 256;', $source);
    }
}
