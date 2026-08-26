<?php
declare(strict_types=1);
namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Http;
use PHPUnit\Framework\TestCase;
final class ConditionAjaxRegressionTest extends TestCase {
 public function testProviderRegistersConditionEndpoints():void {
  $s=(string)file_get_contents(dirname(__DIR__,4).'/app/Tabletop/TabletopServiceProvider.php');
  self::assertStringContainsString('wp_ajax_gmrt_apply_condition',$s);
  self::assertStringContainsString('wp_ajax_gmrt_remove_condition',$s);
 }
 public function testStateEndpointExposesConditions():void {
  $s=(string)file_get_contents(dirname(__DIR__,4).'/app/Tabletop/Http/TabletopAjaxController.php');
  self::assertStringContainsString("'conditions' => \$state->conditions()",$s);
 }
}
