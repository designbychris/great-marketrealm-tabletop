<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class WallAndRoomMustAgreeRegressionTest extends TestCase
{
    public function test_g5z50k_compares_fixed_route_and_recovery_interface_without_moving_either(): void
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertIsString($source);
        self::assertStringContainsString('IV.30.1G.5Z.50K — The Wall and the Room Must Agree.', $source);
        self::assertStringContainsString('const barrierAgreementReviews=', $source);
        self::assertStringContainsString('const offsets=shape.trackOffsets.slice();', $source);
        self::assertStringContainsString('route-crosses-recovery-interface-review', $source);
        self::assertStringContainsString('route-drifts-relative-to-recovery-interface-review', $source);
        self::assertStringContainsString('stable-negative-offset-route-review', $source);
        self::assertStringContainsString('stable-positive-offset-route-review', $source);
        self::assertStringContainsString('candidate-facing-lost-at-route-frame-review', $source);
        self::assertStringContainsString('control-floor-both-agrees-review', $source);
        self::assertStringContainsString('G.5Z.50K wall and room agreement', $source);
        self::assertStringContainsString('fixed-route-versus-recovery-interface-frame-agreement-not-wall-certification;no-route-shift;no-nearest-wall-search;no-recovery-replay;G.5Z.50-veto-unchanged', $source);
        self::assertStringContainsString('const maximumReviewSuggestions = 200;', $source);
        self::assertStringContainsString('const reconstructedSurfaceReviewCeiling = maximumReviewSuggestions;', $source);
        self::assertStringContainsString('const maximumPathVertices = 256;', $source);
    }
}
