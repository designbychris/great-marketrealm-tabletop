<?php
declare(strict_types=1);
namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Fog;
use PHPUnit\Framework\TestCase;
final class FogPreviewPersistenceRegressionTest extends TestCase
{
 public function testDmPreviewSurvivesEndTurnReload():void
 {
  $s=(string)file_get_contents(dirname(__DIR__,4).'/assets/js/tabletop.js');
  self::assertStringContainsString('window.sessionStorage.getItem(',$s);
  self::assertStringContainsString('window.sessionStorage.setItem(',$s);
  self::assertStringContainsString('gmrt-fog-preview:',$s);
 }
 public function testUnanchoredFogPromptsForOneGridSave():void
 {
  $s=(string)file_get_contents(dirname(__DIR__,4).'/assets/js/tabletop.js');
  self::assertStringContainsString('Save Grid once to anchor Fog of War',$s);
 }
}
