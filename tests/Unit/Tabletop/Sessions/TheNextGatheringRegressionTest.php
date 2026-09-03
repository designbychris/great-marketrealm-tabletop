<?php

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Sessions;

use PHPUnit\Framework\TestCase;

final class TheNextGatheringRegressionTest extends TestCase
{
    private function source(string $path): string
    {
        return (string) file_get_contents(dirname(__DIR__, 4) . '/' . $path);
    }

    public function test_keeper_receives_next_gathering_handover_from_latest_completed_session(): void
    {
        $view = $this->source('app/Tabletop/Views/chamber.php');

        self::assertStringContainsString('data-next-gathering', $view);
        self::assertStringContainsString('The Next Gathering', $view);
        self::assertStringContainsString('The Table remembers where you left it.', $view);
        self::assertStringContainsString('Call the Next Gathering', $view);
        self::assertStringContainsString("(int) (\$sessionRecap['number'] ?? 0) + 1", $view);
    }

    public function test_previous_recap_remains_available_after_the_next_session_begins(): void
    {
        $view = $this->source('app/Tabletop/Views/chamber.php');

        self::assertStringContainsString("<?php if (\$sessionRecap !== null) : ?>", $view);
        self::assertStringContainsString('gmrt-session-recap--previous', $view);
        self::assertStringContainsString('Previous Session · Pippin Peppercorn presents', $view);
    }

    public function test_starting_next_gathering_reuses_authoritative_session_lifecycle_without_resetting_campaign_state(): void
    {
        $script = $this->source('assets/js/tabletop.js');
        $start = strpos($script, "request('gmrt_start_table_session'");
        self::assertNotFalse($start);

        $window = substr($script, max(0, $start - 700), 1500);
        self::assertStringContainsString("request('gmrt_start_table_session', { title })", $window);
        self::assertStringContainsString("replaceChamber('The Session has begun — the Table remembers tonight.'", $window);
        self::assertStringNotContainsString('reset', strtolower($window));
        self::assertStringNotContainsString('delete', strtolower($window));
        self::assertStringNotContainsString('clear', strtolower($window));
    }
}
