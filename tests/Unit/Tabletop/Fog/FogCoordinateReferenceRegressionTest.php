<?php
declare(strict_types=1);
namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Fog;
use PHPUnit\Framework\TestCase;
final class FogCoordinateReferenceRegressionTest extends TestCase
{
 public function testServerScalesVisualGridIntoNativeMapCoordinates():void
 {
  $s=(string)file_get_contents(dirname(__DIR__,4).'/app/Tabletop/Fog/Services/FogCellMapper.php');
  self::assertStringContainsString('$scene->gridReferenceWidth()',$s);
  self::assertStringContainsString('$scene->width() / $referenceWidth',$s);
  self::assertStringContainsString('$scene->gridSize() * $scale',$s);
 }
 public function testProjectionExposesGridReferenceWidth():void
 {
  $s=(string)file_get_contents(dirname(__DIR__,4).'/app/Tabletop/Fog/Services/FogOfWarProjector.php');
  self::assertStringContainsString("'reference_width' => " . '$scene->gridReferenceWidth()', $s);
 }
 public function testClientScalesFogToRenderedBattlefield():void
 {
  $s=(string)file_get_contents(dirname(__DIR__,4).'/assets/js/tabletop.js');
  self::assertStringContainsString('displayWidth / referenceWidth',$s);
  self::assertStringContainsString('grid_reference_width:',$s);
  self::assertStringContainsString('gridViewport?.clientWidth',$s);
 }
}
