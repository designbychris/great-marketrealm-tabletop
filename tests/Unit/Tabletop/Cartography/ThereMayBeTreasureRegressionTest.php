<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class ThereMayBeTreasureRegressionTest extends TestCase
{
    private function root(string $path): string
    {
        return dirname(__DIR__, 4) . '/' . $path;
    }

    public function test_both_forge_surfaces_offer_optional_treasure(): void
    {
        $view = file_get_contents($this->root('app/Tabletop/Views/chamber.php'));
        self::assertIsString($view);
        self::assertStringContainsString('data-atlas-forge-treasure', $view);
        self::assertStringContainsString('data-dungeon-forge-treasure', $view);
        self::assertStringContainsString('Include treasure', $view);
    }

    public function test_player_boundary_only_exposes_revealed_treasure(): void
    {
        $view = file_get_contents($this->root('app/Tabletop/Views/chamber.php'));
        $ajax = file_get_contents($this->root('app/Tabletop/Http/TabletopAjaxController.php'));
        self::assertIsString($view);
        self::assertIsString($ajax);
        self::assertStringContainsString('$dungeonForge[\'treasure\']=array_values(array_filter', $view);
        self::assertStringContainsString('$forge[\'treasure\'] = array_values(array_filter', $ajax);
        self::assertStringContainsString('! empty($treasure[\'revealed\'])', $ajax);
    }

    public function test_keeper_has_a_treasure_ledger_and_map_markers(): void
    {
        $view = file_get_contents($this->root('app/Tabletop/Views/chamber.php'));
        self::assertIsString($view);
        self::assertStringContainsString("The Keeper's Treasure Ledger", $view);
        self::assertStringContainsString('data-treasure-place', $view);
        self::assertStringContainsString('data-treasure-roster', $view);
        self::assertStringContainsString('data-forge-treasure-id', $view);
    }

    public function test_treasure_lifecycle_supports_reveal_loot_reset_edit_move_and_remove(): void
    {
        $controller = file_get_contents($this->root('app/Tabletop/Http/DungeonForgeAjaxController.php'));
        self::assertIsString($controller);
        self::assertStringContainsString("['add', 'update', 'move', 'remove', 'reveal', 'conceal', 'loot', 'reset']", $controller);
        self::assertStringContainsString('$projection[\'treasure\'][$foundIndex][\'looted\'] = true', $controller);
        self::assertStringContainsString('$projection[\'treasure\'][$foundIndex][\'revealed\'] = false', $controller);
        self::assertStringContainsString('array_splice($projection[\'treasure\']', $controller);
    }

    public function test_treasure_actions_use_the_active_scene_and_chamber_refresh(): void
    {
        $javascript = file_get_contents($this->root('assets/js/tabletop.js'));
        self::assertIsString($javascript);
        self::assertStringContainsString("request('gmrt_forge_treasure_action'", $javascript);
        self::assertStringContainsString('scene_id: treasureSceneId()', $javascript);
        self::assertStringContainsString('await replaceChamber', $javascript);
    }

    public function test_phase_does_not_create_a_second_inventory_or_currency_system(): void
    {
        $planner = file_get_contents($this->root('app/Tabletop/Cartography/Services/ForgeTreasurePlanner.php'));
        self::assertIsString($planner);
        self::assertStringNotContainsString('CoinPurse', $planner);
        self::assertStringNotContainsString('InventoryRepository', $planner);
        self::assertStringContainsString('preparation metadata rather than inventory', $planner);
    }
}
