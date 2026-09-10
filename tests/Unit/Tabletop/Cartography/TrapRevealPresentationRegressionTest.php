<?php
declare(strict_types=1);
namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Cartography;
use PHPUnit\Framework\TestCase;
final class TrapRevealPresentationRegressionTest extends TestCase
{
    public function test_player_ajax_projection_filters_unrevealed_traps_and_secrets():void
    {
        $source=file_get_contents(dirname(__DIR__,4).'/app/Tabletop/Http/TabletopAjaxController.php');
        self::assertIsString($source);
        self::assertStringContainsString('visibleIntegrations(', $source);
        self::assertStringContainsString("! empty(\$trap['revealed'])", $source);
        self::assertStringContainsString("'forge_revision' => \$this->forgeRevision(\$integrations)", $source);
    }
    public function test_live_refresh_watches_only_visible_forge_revision():void
    {
        $source=file_get_contents(dirname(__DIR__,4).'/assets/js/tabletop.js');
        self::assertIsString($source);
        self::assertStringContainsString('incomingForgeRevision', $source);
        self::assertStringContainsString('root.dataset.forgeRevision', $source);
        self::assertStringContainsString('Something in the dungeon has changed.', $source);
    }
    public function test_concealed_and_revealed_traps_have_distinct_keeper_markers():void
    {
        $view=file_get_contents(dirname(__DIR__,4).'/app/Tabletop/Views/chamber.php');
        self::assertIsString($view);
        self::assertStringContainsString("!\$trapRevealed?'?'", $view);
        self::assertStringContainsString("' is-concealed'", $view);
    }
    public function test_trap_controls_touch_the_marker_hover_target():void
    {
        $css=file_get_contents(dirname(__DIR__,4).'/assets/css/tabletop.css');
        self::assertIsString($css);
        self::assertStringContainsString('left:calc(100% - 2px)', $css);
        self::assertStringContainsString('.gmrt-forge-trap-controls::before', $css);
    }
}
