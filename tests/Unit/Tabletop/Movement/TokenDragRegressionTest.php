<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Movement;

use PHPUnit\Framework\TestCase;

final class TokenDragRegressionTest extends TestCase
{
    public function testTokenDragOwnsPointerAndCommitsThroughServerMovement(): void
    {
        $source = (string) file_get_contents(
            dirname(__DIR__, 4) . '/assets/js/tabletop.js'
        );

        self::assertStringContainsString("token.addEventListener('pointerdown'", $source);
        self::assertStringContainsString("token.addEventListener('pointermove'", $source);
        self::assertStringContainsString("token.addEventListener('pointerup'", $source);
        self::assertStringContainsString('token.setPointerCapture(event.pointerId)', $source);
        self::assertStringContainsString('moveSelected(point.x, point.y)', $source);
        self::assertStringContainsString("request('gmrt_move_token'", $source);
    }

    public function testTokensRenderAboveTheVisualVeilWithoutChangingFogFiltering(): void
    {
        $css = (string) file_get_contents(
            dirname(__DIR__, 4) . '/assets/css/tabletop.css'
        );
        $chamber = (string) file_get_contents(
            dirname(__DIR__, 4) . '/app/Tabletop/Services/TabletopChamber.php'
        );

        self::assertStringContainsString(".gmrt-board__tokens {\n    z-index: 9;", $css);
        self::assertStringContainsString('tokenIsCurrentlyVisible', $chamber);
    }
}
