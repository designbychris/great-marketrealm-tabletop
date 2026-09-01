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

    public function test_generated_barriers_are_converted_from_surface_to_rules_grid_coordinates(): void
    {
        $controller = file_get_contents(dirname(__DIR__, 4) . '/app/Tabletop/Http/DungeonForgeAjaxController.php');
        self::assertIsString($controller);

        self::assertStringContainsString("'x1' => \$this->clamp((float) (\$barrier['x1'] ?? 0)) * \$cols", $controller);
        self::assertStringContainsString("'y1' => \$this->clamp((float) (\$barrier['y1'] ?? 0)) * \$rows", $controller);
        self::assertStringContainsString("'x2' => \$this->clamp((float) (\$barrier['x2'] ?? 0)) * \$cols", $controller);
        self::assertStringContainsString("'y2' => \$this->clamp((float) (\$barrier['y2'] ?? 0)) * \$rows", $controller);
    }

    public function test_generated_door_and_light_surface_coordinates_remain_normalised(): void
    {
        $controller = file_get_contents(dirname(__DIR__, 4) . '/app/Tabletop/Http/DungeonForgeAjaxController.php');
        self::assertIsString($controller);

        self::assertStringContainsString("\$doors[] = [\n                'x1' => \$this->clamp", $controller);
        self::assertStringContainsString("'x' => \$this->clamp((float) (\$light['x'] ?? 0))", $controller);
        self::assertStringContainsString("'y' => \$this->clamp((float) (\$light['y'] ?? 0))", $controller);
    }

    public function test_corner_tile_corrective_keeps_the_certified_plugin_version(): void
    {
        $plugin = file_get_contents(dirname(__DIR__, 4) . '/great-marketrealm-tabletop.php');
        self::assertIsString($plugin);
        self::assertStringContainsString('0.32.0-alpha.8', $plugin);
    }
}
