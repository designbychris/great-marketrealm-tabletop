<?php

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class DungeonForgeCornerForensicsRegressionTest extends TestCase
{
    public function test_generated_scene_exposes_keeper_corner_trace(): void
    {
        $view = file_get_contents(dirname(__DIR__, 4) . '/app/Tabletop/Views/chamber.php');
        $js = file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertStringContainsString('data-corner-forensics', $view);
        self::assertStringContainsString('document.elementsFromPoint(x, y)', $js);
        self::assertStringContainsString("board.querySelectorAll('*')", $js);
        self::assertStringContainsString("style.pointerEvents === 'none'", $js);
        self::assertStringContainsString("[data-token-id], img, [data-light-id]", $js);
        self::assertStringContainsString("[xy=' + (tokenX || '?')", $js);
        self::assertStringContainsString('MutationObserver', $js);
        self::assertStringContainsString('document.caretRangeFromPoint', $js);
        self::assertStringContainsString('root.dataset.cornerTrace', $js);
    }
}
