<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class KeeperTrapCabinetRegressionTest extends TestCase
{
    public function test_dm_controls_contains_a_keeper_trap_cabinet(): void
    {
        $view = file_get_contents(dirname(__DIR__, 4) . '/app/Tabletop/Views/chamber.php');
        self::assertIsString($view);
        self::assertStringContainsString("The Keeper's Trap Cabinet", $view);
        self::assertStringContainsString('data-trap-place', $view);
        self::assertStringContainsString('data-trap-roster', $view);
    }

    public function test_trap_cabinet_supports_add_edit_move_and_remove(): void
    {
        $controller = file_get_contents(dirname(__DIR__, 4) . '/app/Tabletop/Http/DungeonForgeAjaxController.php');
        self::assertIsString($controller);
        self::assertStringContainsString("'add', 'update', 'move', 'remove'", $controller);
        self::assertStringContainsString("'manual' => true", $controller);
        self::assertStringContainsString("array_splice(\$projection['traps']", $controller);
    }

    public function test_reset_means_rearm_and_conceal(): void
    {
        $controller = file_get_contents(dirname(__DIR__, 4) . '/app/Tabletop/Http/DungeonForgeAjaxController.php');
        self::assertIsString($controller);
        self::assertStringContainsString("if (\$action === 'reset')", $controller);
        self::assertStringContainsString("\$projection['traps'][\$foundIndex]['revealed'] = false", $controller);
        self::assertStringContainsString("\$projection['traps'][\$foundIndex]['armed'] = true", $controller);
        self::assertStringContainsString("'reset' => 'Trap reset, re-armed and concealed.'", $controller);
    }

    public function test_rearm_is_distinct_from_full_reset(): void
    {
        $controller = file_get_contents(dirname(__DIR__, 4) . '/app/Tabletop/Http/DungeonForgeAjaxController.php');
        self::assertIsString($controller);
        self::assertStringContainsString("if (\$action === 'rearm')", $controller);
        self::assertStringContainsString("'rearm' => 'Trap re-armed and left revealed.'", $controller);
        self::assertStringContainsString('Reset &amp; Conceal', file_get_contents(dirname(__DIR__, 4) . '/app/Tabletop/Views/chamber.php'));
    }

    public function test_trap_actions_send_the_active_scene_id_explicitly(): void
    {
        $javascript = file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertIsString($javascript);
        self::assertStringContainsString('scene_id: preparationSceneId || projectedSceneId', $javascript);
        self::assertStringContainsString("request('gmrt_forge_trap_action'", $javascript);
    }

    public function test_trap_placement_claims_the_battlefield_pointer(): void
    {
        $javascript = file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertIsString($javascript);
        self::assertStringContainsString("board?.classList.add('is-trap-placing')", $javascript);
        self::assertStringContainsString('event.stopImmediatePropagation()', $javascript);
        self::assertStringContainsString("trap_action: placement.mode === 'move' ? 'move' : 'add'", $javascript);
    }
}
