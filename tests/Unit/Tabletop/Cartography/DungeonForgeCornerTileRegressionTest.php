<?php

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class DungeonForgeCornerTileRegressionTest extends TestCase
{
    public function test_atlas_toggle_does_not_render_the_colour_map_emoji_over_the_battlefield(): void
    {
        $view = file_get_contents(dirname(__DIR__, 4) . '/app/Tabletop/Views/chamber.php');
        self::assertIsString($view);
        self::assertStringContainsString('data-atlas-toggle', $view);
        self::assertStringNotContainsString('🗺️', $view);
        self::assertStringContainsString('<span>Atlas</span>', $view);
    }

    public function test_corner_tile_corrective_advances_the_plugin_version(): void
    {
        $plugin = file_get_contents(dirname(__DIR__, 4) . '/great-marketrealm-tabletop.php');
        self::assertIsString($plugin);
        self::assertStringContainsString('0.32.0-alpha.6', $plugin);
    }
}
