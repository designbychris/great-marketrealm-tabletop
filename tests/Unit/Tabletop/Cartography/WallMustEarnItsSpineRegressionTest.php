<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class WallMustEarnItsSpineRegressionTest extends TestCase
{
    private function source(): string
    {
        return (string) file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
    }

    public function test_replay_preserves_original_eighteen_conditions_and_production_gate(): void
    {
        $source = $this->source();
        self::assertStringContainsString('IV.30.1G.5Z.50Y — The Wall Must Earn Its Spine.', $source);
        self::assertStringContainsString("original.total-failed.length", $source);
        self::assertStringContainsString("original.failed.filter((name)=>name!=='bounded-spine-review')", $source);
        self::assertStringContainsString('G.5Z.50V-gate-unchanged', $source);
    }

    public function test_replay_reports_control_failures_and_fixed_route_support(): void
    {
        $source = $this->source();
        self::assertStringContainsString('illustratedPropagationBarrierEarnedSpineReplayReviews,', $source);
        self::assertStringContainsString('illustratedPropagationBarrierEarnedSpineControlFailures,', $source);
        self::assertStringContainsString('alignment.supportedRoute===alignment.length', $source);
    }
}
