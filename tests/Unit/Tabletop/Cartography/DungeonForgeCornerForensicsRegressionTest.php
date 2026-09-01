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
        self::assertStringContainsString('Corner trace III:', $js);
        self::assertStringContainsString("board.querySelectorAll('*')", $js);
        self::assertStringContainsString('getBoundingClientRect()', $js);
        self::assertStringContainsString("window.getComputedStyle(el, '::before')", $js);
        self::assertStringContainsString('style.backgroundImage', $js);
        self::assertStringContainsString('style.boxShadow', $js);
        self::assertStringContainsString('style.clipPath', $js);
        self::assertStringContainsString('SVGImageElement', $js);
        self::assertStringContainsString('document.elementFromPoint(x, y)', $js);
                                self::assertStringContainsString('MutationObserver', $js);
                self::assertStringContainsString('root.dataset.cornerTrace', $js);
    }
}
