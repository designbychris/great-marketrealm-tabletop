<?php
declare(strict_types=1);
namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Presentation;
use PHPUnit\Framework\TestCase;
final class TabletopConditionViewRegressionTest extends TestCase {
 public function testDungeonMasterHasAfflictionControls():void {
  $s=(string)file_get_contents(dirname(__DIR__,4).'/app/Tabletop/Views/chamber.php');
  self::assertStringContainsString('data-affliction-controls',$s);
  self::assertStringContainsString('data-apply-condition',$s);
  self::assertStringContainsString('data-remove-condition',$s);
 }
 public function testTokensRenderConditionMarkers():void {
  $s=(string)file_get_contents(dirname(__DIR__,4).'/app/Tabletop/Views/chamber.php');
  self::assertStringContainsString('gmrt-token__conditions',$s);
 }
 public function testPoisonUsesPixelBubbleAnimation():void {
  $s=(string)file_get_contents(dirname(__DIR__,4).'/assets/css/tabletop.css');
  self::assertStringContainsString('.gmrt-condition--poisoned',$s);
  self::assertStringContainsString('@keyframes gmrt-poison-bubble',$s);
 }
 public function testReducedMotionStillDisablesConditionAnimation():void {
  $s=(string)file_get_contents(dirname(__DIR__,4).'/assets/css/tabletop.css');
  self::assertStringContainsString('@media (prefers-reduced-motion: reduce)',$s);
 }
}
