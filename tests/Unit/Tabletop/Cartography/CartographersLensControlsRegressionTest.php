<?php
declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class CartographersLensControlsRegressionTest extends TestCase
{
    public function testLensControlsLiveInsideBattlemapStage(): void
    {
        $s = (string) file_get_contents(dirname(__DIR__, 4) . '/app/Tabletop/Views/chamber.php');
        $stage = strpos($s, 'data-lens-stage');
        $controls = strpos($s, 'data-lens-controls');
        $viewport = strpos($s, 'gmrt-board__viewport', $stage ?: 0);

        self::assertNotFalse($stage);
        self::assertNotFalse($controls);
        self::assertNotFalse($viewport);
        self::assertGreaterThan($stage, $controls);
        self::assertLessThan($viewport, $controls);
    }

    public function testOverlayExposesAccessibleZoomFitAndResetControls(): void
    {
        $s = (string) file_get_contents(dirname(__DIR__, 4) . '/app/Tabletop/Views/chamber.php');
        self::assertStringContainsString('aria-label="Battlemap view controls"', $s);
        self::assertStringContainsString('data-lens-zoom-in', $s);
        self::assertStringContainsString('data-lens-zoom-out', $s);
        self::assertStringContainsString('data-lens-zoom', $s);
        self::assertStringContainsString('data-lens-fit', $s);
        self::assertStringContainsString('data-lens-reset', $s);
    }

    public function testOverlayRemainsFixedWhileViewportTransformsBeneathIt(): void
    {
        $s = (string) file_get_contents(dirname(__DIR__, 4) . '/assets/css/tabletop.css');
        self::assertStringContainsString('.gmrt-board__lens-controls', $s);
        self::assertStringContainsString('position: absolute', $s);
        self::assertStringContainsString('z-index: 45', $s);
        self::assertStringContainsString('.gmrt-board__lens-stage .gmrt-board__viewport', $s);
    }

    public function testControlsReuseBoundedLensStateAndFitGutter(): void
    {
        $s = (string) file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertStringContainsString('const zoomLens', $s);
        self::assertStringContainsString('const fitLens', $s);
        self::assertStringContainsString('const resetLens', $s);
        self::assertStringContainsString('const fitPadding = 24', $s);
        self::assertStringContainsString('lensZoomOut.disabled', $s);
        self::assertStringContainsString('lensZoomIn.disabled', $s);
        self::assertStringContainsString('renderLens();', $s);
    }

    public function testPhaseRemainsClientOnlyAndDocumented(): void
    {
        $js = (string) file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        $roadmap = (string) file_get_contents(dirname(__DIR__, 4) . '/ROADMAP.md');
        $phase = (string) file_get_contents(dirname(__DIR__, 4) . '/docs/Roadmap/PHASE-IV.30.1E.md');
        $plugin = (string) file_get_contents(dirname(__DIR__, 4) . '/great-marketrealm-tabletop.php');

        self::assertStringNotContainsString('gmrt_save_lens', $js);
        self::assertStringContainsString("IV.30.1E — The Cartographer's Lens Controls", $roadmap);
        self::assertStringContainsString('no server mutation route', $phase);
        self::assertStringContainsString('0.32.0-alpha.4', $plugin);
    }
}
