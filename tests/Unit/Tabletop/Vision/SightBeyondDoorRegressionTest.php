<?php
declare(strict_types=1);
namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Vision;
use PHPUnit\Framework\TestCase;
final class SightBeyondDoorRegressionTest extends TestCase
{
    public function testChamberUsesPersistedVisionBarriersForFogProjection():void{$s=(string)file_get_contents(dirname(__DIR__,4).'/app/Tabletop/Services/TabletopChamber.php');self::assertStringContainsString('VisionBarrierRepository',$s);self::assertStringContainsString('$barrierModels',$s);self::assertStringContainsString('$visionLayer',$s);}
    public function testPlayerPayloadDoesNotReceiveDmVisionLayerGeometry():void{$s=(string)file_get_contents(dirname(__DIR__,4).'/app/Tabletop/Services/TabletopChamber.php');self::assertStringContainsString('if ($viewer->isDungeonMaster())',$s);self::assertStringContainsString('$visionLayer = array_map',$s);}
    public function testClientStopsRebuildingCircularVisionWhenBlockersExist():void{$s=(string)file_get_contents(dirname(__DIR__,4).'/assets/js/tabletop.js');self::assertStringContainsString('fogProjection.has_blockers',$s);self::assertStringContainsString("request('gmrt_add_vision_barrier'",$s);self::assertStringContainsString("request('gmrt_toggle_vision_door'",$s);}
    public function testDmHasWallAndDoorCartographyControls():void{$s=(string)file_get_contents(dirname(__DIR__,4).'/app/Tabletop/Views/chamber.php');self::assertStringContainsString('Sight Beyond the Door',$s);self::assertStringContainsString('data-vision-tool="wall"',$s);self::assertStringContainsString('data-vision-tool="door"',$s);}
    public function testAjaxWiringRegistersVisionActions():void{$s=(string)file_get_contents(dirname(__DIR__,4).'/app/Tabletop/TabletopServiceProvider.php');self::assertStringContainsString('wp_ajax_gmrt_add_vision_barrier',$s);self::assertStringContainsString('wp_ajax_gmrt_toggle_vision_door',$s);self::assertStringContainsString('wp_ajax_gmrt_remove_vision_barrier',$s);}
    public function testExplorationMemoryRefreshUsesBlockerAwareSight():void{$s=(string)file_get_contents(dirname(__DIR__,4).'/app/Tabletop/Vision/Services/VisionBarrierManager.php');self::assertStringContainsString('refreshExploration',$s);self::assertStringContainsString('visibleAround($scene,$token,$barriers)',$s);self::assertStringContainsString('$this->fog->save($tableId,$state)',$s);}
}
