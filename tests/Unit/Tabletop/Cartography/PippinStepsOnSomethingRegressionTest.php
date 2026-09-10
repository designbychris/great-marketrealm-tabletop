<?php
declare(strict_types=1);
namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Cartography;
use PHPUnit\Framework\TestCase;
final class PippinStepsOnSomethingRegressionTest extends TestCase
{
    public function test_both_forge_surfaces_offer_traps_and_player_boundary_filters_them():void
    {
        $view=file_get_contents(dirname(__DIR__,4).'/app/Tabletop/Views/chamber.php');
        self::assertIsString($view);self::assertStringContainsString('data-atlas-forge-traps',$view);self::assertStringContainsString('data-dungeon-forge-traps',$view);
        self::assertStringContainsString("\$dungeonForge['traps']=array_values(array_filter",$view);
    }
    public function test_movement_and_keeper_actions_share_persisted_trap_state():void
    {
        $movement=file_get_contents(dirname(__DIR__,4).'/app/Tabletop/Movement/Services/TabletopMovement.php');
        $controller=file_get_contents(dirname(__DIR__,4).'/app/Tabletop/Http/DungeonForgeAjaxController.php');
        self::assertStringContainsString('afterMovement($member,$token,$fromX,$fromY)',$movement);
        self::assertStringContainsString("['reveal','disarm','trigger','reset']",$controller);
        self::assertStringContainsString("'traps' => \$trapDrafts",$controller);
    }
}
