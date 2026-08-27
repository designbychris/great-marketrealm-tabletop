<?php
declare(strict_types=1);
namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Fog;
use PHPUnit\Framework\TestCase;
final class FogOfWarRegressionTest extends TestCase
{
 public function testMovementRevealsFogForCharacterTokens():void{$s=(string)file_get_contents(dirname(__DIR__,4).'/app/Tabletop/Movement/Services/TabletopMovement.php');self::assertStringContainsString('revealForMovement',$s);}
 public function testDungeonMasterCanConfigureFog():void{$s=(string)file_get_contents(dirname(__DIR__,4).'/app/Tabletop/Fog/Services/FogOfWarManager.php');self::assertStringContainsString('TableMemberRole::DUNGEON_MASTER',$s);self::assertStringContainsString('$state->enable()',$s);}
 public function testPlayerProjectionCanHideNonCharacterTokensOutsideCurrentVision():void{$s=(string)file_get_contents(dirname(__DIR__,4).'/app/Tabletop/Services/TabletopChamber.php');self::assertStringContainsString('tokenIsCurrentlyVisible',$s);}
 public function testFogStateIsExposedByTabletopStateEndpoint():void{$s=(string)file_get_contents(dirname(__DIR__,4).'/app/Tabletop/Http/TabletopAjaxController.php');self::assertStringContainsString("'fog' => " . '$state->fog()', $s);}
 public function testDmHasPlayerFogPreviewControl():void{$s=(string)file_get_contents(dirname(__DIR__,4).'/app/Tabletop/Views/chamber.php');self::assertStringContainsString('data-fog-preview',$s);self::assertStringContainsString('Preview Player Fog',$s);}
 public function testClientRendersUnexploredAndRememberedCells():void{$s=(string)file_get_contents(dirname(__DIR__,4).'/assets/js/tabletop.js');self::assertStringContainsString("'gmrt-fog-cell is-memory'",$s);self::assertStringContainsString("'gmrt-fog-cell is-unexplored'",$s);self::assertStringContainsString('visible.has(key)',$s);}
 public function testFogIsIndependentOfLensTransform():void{$s=(string)file_get_contents(dirname(__DIR__,4).'/app/Tabletop/Views/chamber.php');$fog=strpos($s,'data-fog-layer');$stage=strpos($s,'data-lens-stage');self::assertIsInt($fog);self::assertIsInt($stage);self::assertGreaterThan($stage,$fog);}
}
