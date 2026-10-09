<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class CornerRightfulMeetingPointRegressionTest extends TestCase
{
    public function test_meeting_point_remains_bounded_and_diagnostic_only(): void
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertIsString($source);
        self::assertStringContainsString("IV.30.1G.5Z.50Z.8 — The Corner's Rightful Meeting Point.", $source);
        self::assertStringContainsString('barrierCornerHandshakeReviews.map((review)', $source);
        self::assertStringContainsString('shared-proposed-target-without-structural-join-review', $source);
        self::assertStringContainsString('illustratedPropagationBarrierCornerMeetingPointReviews,', $source);
        self::assertStringContainsString('diagnostic-only;bounded-structural-meeting-hypothesis;no-snapping;no-wall-admission;no-geometry-mutation;G.5Z.50-veto-unchanged', $source);
    }
}
