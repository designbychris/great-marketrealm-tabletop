<?php

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class DungeonForgeCornerForensicsRegressionTest extends TestCase
{
    public function test_temporary_corner_forensics_are_removed_after_root_cause_is_fixed(): void
    {
        $view = file_get_contents(dirname(__DIR__, 4) . '/app/Tabletop/Views/chamber.php');
        $js = file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        $css = file_get_contents(dirname(__DIR__, 4) . '/assets/css/tabletop.css');

        self::assertIsString($view);
        self::assertIsString($js);
        self::assertIsString($css);
        self::assertStringNotContainsString('data-corner-forensics', $view);
        self::assertStringNotContainsString('Corner trace', $js);
        self::assertStringNotContainsString('traceGeneratedCorner', $js);
        self::assertStringNotContainsString('cornerTrace', $js);
        self::assertStringNotContainsString('.gmrt-corner-forensics', $css);
    }
}
