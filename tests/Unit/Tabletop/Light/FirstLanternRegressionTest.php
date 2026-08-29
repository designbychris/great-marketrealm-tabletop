<?php
declare(strict_types=1);
namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Light;
use PHPUnit\Framework\TestCase;
final class FirstLanternRegressionTest extends TestCase
{
    public function testFirstLanternRemainsServerAuthoritativeAndBarrierAware(): void
    {
        $root=dirname(__DIR__,4);
        $fog=file_get_contents($root.'/app/Tabletop/Fog/Services/FogOfWarProjector.php');
        $controller=file_get_contents($root.'/app/Tabletop/Http/CarriedLightAjaxController.php');
        $js=file_get_contents($root.'/assets/js/tabletop.js');
        self::assertStringContainsString("'light_radius_feet'", $fog);
        self::assertStringContainsString('max(15, $darkvisionFeet, $lightFeet)', $fog);
        self::assertStringContainsString('visibleAround(', $fog);
        self::assertStringContainsString("'range_feet'=>40", $controller);
        self::assertStringContainsString('controllerUserId()===$userId', $controller);
        self::assertStringContainsString("gmrt_toggle_carried_light", $js);
        self::assertStringNotContainsString('light_radius_feet:', $js);
    }
}
