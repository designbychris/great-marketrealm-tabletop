<?php
declare(strict_types=1);
namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Light;
use PHPUnit\Framework\TestCase;
final class FireUponTheFloorRegressionTest extends TestCase
{
    public function testDroppedTorchPersistsAsSceneScopedWorldLight(): void {
        $root=dirname(__DIR__,4); $repo=(string)file_get_contents($root.'/app/Tabletop/Light/Repositories/WordPressDroppedLightRepository.php'); $chamber=(string)file_get_contents($root.'/app/Tabletop/Services/TabletopChamber.php');
        self::assertStringContainsString("gmrt_dropped_lights",$repo);
        self::assertStringContainsString('forScene(string $tableId,string $sceneId)',$repo);
        self::assertStringContainsString('$this->droppedLights->forScene($tableId, $activeScene->id())',$chamber);
        self::assertStringContainsString('$worldLightSourceModels[] = $droppedLight;',$chamber);
        self::assertStringContainsString('WordPressDroppedLightRepository',$root ? (string)file_get_contents($root.'/app/Tabletop/Services/TabletopChamberFactory.php') : '');
    }
    public function testDropAndPickupAreServerAuthoritative(): void {
        $root=dirname(__DIR__,4); $controller=(string)file_get_contents($root.'/app/Tabletop/Http/DroppedLightAjaxController.php'); $js=(string)file_get_contents($root.'/assets/js/tabletop.js');
        self::assertStringContainsString('$action=sanitize_key',$controller);
        self::assertStringContainsString('$token->x(),$token->y()',$controller);
        self::assertStringContainsString("\$action==='pickup'",$controller);
        self::assertStringContainsString('hypot(',$controller);
        self::assertStringContainsString("light_action: action",$js);
        self::assertStringNotContainsString("light_x:",$js);
    }
    public function testDroppedTorchUsesSharedBarrierAwareIllumination(): void {
        $root=dirname(__DIR__,4); $fog=(string)file_get_contents($root.'/app/Tabletop/Fog/Services/FogOfWarProjector.php');
        self::assertStringContainsString('instanceof DroppedLight',$fog);
        self::assertStringContainsString("\$sourceKind = 'dropped'",$fog);
        self::assertStringContainsString('array_intersect($illuminated, $viewerLineOfSight)',$fog);
        self::assertStringContainsString("'source_kind' => \$sourceKind",$fog);
    }
}
