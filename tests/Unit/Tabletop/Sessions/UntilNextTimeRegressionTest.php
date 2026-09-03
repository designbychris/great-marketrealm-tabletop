<?php

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Sessions;

use PHPUnit\Framework\TestCase;

final class UntilNextTimeRegressionTest extends TestCase
{
    private function source(string $path): string
    {
        return (string) file_get_contents(dirname(__DIR__, 4) . '/' . $path);
    }

    public function test_completed_session_projection_carries_timing_for_the_farewell(): void
    {
        $source = $this->source('app/Tabletop/Services/TabletopChamber.php');

        self::assertStringContainsString("'started_at' => \$startedAt->format(DATE_ATOM)", $source);
        self::assertStringContainsString("'ended_at' => \$endedAt?->format(DATE_ATOM)", $source);
        self::assertStringContainsString("'duration_seconds' => \$endedAt !== null", $source);
    }

    public function test_keeper_receives_until_next_time_closing_panel(): void
    {
        $source = $this->source('app/Tabletop/Views/chamber.php');

        self::assertStringContainsString('data-session-closing', $source);
        self::assertStringContainsString('Until Next Time…', $source);
        self::assertStringContainsString('the campaign remains active exactly where you left it', $source);
        self::assertStringContainsString('data-session-closing-view-recap', $source);
        self::assertStringContainsString('data-session-closing-dismiss', $source);
    }

    public function test_farewell_is_transient_and_view_recap_reuses_existing_recap(): void
    {
        $source = $this->source('assets/js/tabletop.js');

        self::assertStringContainsString('rememberSessionClosing(tableId, endingSessionId)', $source);
        self::assertStringContainsString('window.sessionStorage.setItem', $source);
        self::assertStringContainsString('window.sessionStorage.removeItem', $source);
        self::assertStringContainsString('revealFreshSessionClosing()', $source);
        self::assertStringContainsString("document.querySelector('[data-session-recap]')", $source);
        self::assertStringContainsString('setSessionRecapExpanded(recap, true)', $source);
    }
}
