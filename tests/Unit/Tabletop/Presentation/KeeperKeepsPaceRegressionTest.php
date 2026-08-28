<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Presentation;

use PHPUnit\Framework\TestCase;

final class KeeperKeepsPaceRegressionTest extends TestCase
{
    private function tabletopJavascript(): string
    {
        return (string) file_get_contents(
            dirname(__DIR__, 5) . '/assets/js/tabletop.js'
        );
    }

    public function testKeeperTurnAdvanceLetsRefreshObserveTheNewEncounterRevision(): void
    {
        $source = $this->tabletopJavascript();
        $start = strpos($source, "const endTurnButton = document.querySelector('[data-end-turn]');");
        $end = strpos($source, "const applyConditionButton = document.querySelector(", $start);
        self::assertNotFalse($start);
        self::assertNotFalse($end);
        $handler = substr($source, $start, $end - $start);

        self::assertStringContainsString("await request('gmrt_advance_encounter'", $handler);
        self::assertStringContainsString('await refresh();', $handler);
        self::assertStringNotContainsString('encounter.dataset.encounterRevision =', $handler);
    }

    public function testKeeperTurnButtonReturnsFromPassingStateAfterSuccessfulRefresh(): void
    {
        $source = $this->tabletopJavascript();
        $start = strpos($source, "const endTurnButton = document.querySelector('[data-end-turn]');");
        $end = strpos($source, "const applyConditionButton = document.querySelector(", $start);
        self::assertNotFalse($start);
        self::assertNotFalse($end);
        $handler = substr($source, $start, $end - $start);

        self::assertStringContainsString("endTurnButton.textContent = 'Passing…';", $handler);
        self::assertGreaterThanOrEqual(2, substr_count($handler, "endTurnButton.textContent = 'End Turn ▶';"));
        self::assertGreaterThanOrEqual(2, substr_count($handler, 'endTurnButton.disabled = false;'));
    }
}
