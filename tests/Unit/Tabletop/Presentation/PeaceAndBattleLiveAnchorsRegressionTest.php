<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Presentation;

use PHPUnit\Framework\TestCase;

final class PeaceAndBattleLiveAnchorsRegressionTest extends TestCase
{
    private string $view;
    private string $script;

    protected function setUp(): void
    {
        $root = dirname(__DIR__, 4);
        $this->view = (string) file_get_contents($root . '/app/Tabletop/Views/chamber.php');
        $this->script = (string) file_get_contents($root . '/assets/js/tabletop.js');
    }

    public function testChamberProvidesPersistentLifecycleAndChronicleAnchors(): void
    {
        self::assertStringContainsString('data-live-lifecycle', $this->view);
        self::assertStringContainsString('data-live-battle-log-slot', $this->view);
    }

    public function testHeartbeatContinuesToRefreshFogAfterLiveStateChecks(): void
    {
        self::assertStringContainsString('renderFog(state.fog || {});', $this->script);
        self::assertStringContainsString('activeRefreshTimer = window.setInterval(refresh, 5000);', $this->script);
    }

    public function testPlayerLifecycleTransitionDoesNotReplaceWholeChamber(): void
    {
        self::assertStringContainsString('currentLifecycle.replaceChildren', $this->script);
        self::assertStringContainsString('currentLogSlot.replaceChildren', $this->script);
        self::assertStringNotContainsString('currentLifecycle.replaceWith', $this->script);
    }
}
