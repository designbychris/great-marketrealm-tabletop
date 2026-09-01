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
        self::assertStringContainsString('Corner trace IV:', $js);
        self::assertStringContainsString("document.querySelectorAll('body *')", $js);
        self::assertStringContainsString('getBoundingClientRect()', $js);
        self::assertStringContainsString("window.getComputedStyle(el, '::before')", $js);
        self::assertStringContainsString('style.backgroundImage', $js);
        self::assertStringContainsString('style.boxShadow', $js);
        self::assertStringContainsString('document.createTreeWalker(document.body, NodeFilter.SHOW_TEXT)', $js);
        self::assertStringContainsString('SVGImageElement', $js);
        self::assertStringContainsString('document.elementsFromPoint(boardRect.left + dx, boardRect.top + dy)', $js);
        self::assertStringContainsString('MutationObserver', $js);
        self::assertStringContainsString('root.dataset.cornerTrace', $js);
    }
}
