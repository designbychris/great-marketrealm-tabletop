<?php
declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class SteadyLensRegressionTest extends TestCase
{
    public function testBattlemapCannotStartNativeBrowserDrag(): void
    {
        $view = (string) file_get_contents(
            dirname(__DIR__, 4) . '/app/Tabletop/Views/chamber.php'
        );
        $client = (string) file_get_contents(
            dirname(__DIR__, 4) . '/assets/js/tabletop.js'
        );

        self::assertStringContainsString('draggable="false"', $view);
        self::assertStringContainsString("'dragstart'", $client);
        self::assertStringContainsString('event.preventDefault()', $client);
    }

    public function testLensUsesAnchoredPanInsteadOfAccumulatedDeltas(): void
    {
        $source = (string) file_get_contents(
            dirname(__DIR__, 4) . '/assets/js/tabletop.js'
        );

        self::assertStringContainsString('lens.originX + dx', $source);
        self::assertStringContainsString('lens.originY + dy', $source);
        self::assertStringContainsString('lens.startX = event.clientX', $source);
        self::assertStringContainsString('lens.startY = event.clientY', $source);
    }

    public function testLensCapturesPointerForWholeGesture(): void
    {
        $source = (string) file_get_contents(
            dirname(__DIR__, 4) . '/assets/js/tabletop.js'
        );

        self::assertStringContainsString('setPointerCapture(event.pointerId)', $source);
        self::assertStringContainsString('hasPointerCapture(lens.pointerId)', $source);
        self::assertStringContainsString("'lostpointercapture'", $source);
    }

    public function testLensHasMovementThresholdBeforePanning(): void
    {
        $source = (string) file_get_contents(
            dirname(__DIR__, 4) . '/assets/js/tabletop.js'
        );

        self::assertStringContainsString('threshold: 4', $source);
        self::assertStringContainsString('Math.hypot(dx, dy)', $source);
    }

    public function testTokensRemainExcludedFromCameraPanGesture(): void
    {
        $source = (string) file_get_contents(
            dirname(__DIR__, 4) . '/assets/js/tabletop.js'
        );

        self::assertStringContainsString('[data-token-id]', $source);
        self::assertStringContainsString('isLensInteractiveTarget', $source);
    }

    public function testMapDisablesNativeSelectionAndWebkitDragging(): void
    {
        $source = (string) file_get_contents(
            dirname(__DIR__, 4) . '/assets/css/tabletop.css'
        );

        self::assertStringContainsString('-webkit-user-drag: none', $source);
        self::assertStringContainsString('user-select: none', $source);
        self::assertStringContainsString('overscroll-behavior: contain', $source);
    }
}
