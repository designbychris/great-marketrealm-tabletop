<?php
declare(strict_types=1);
namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Conditions\Services;
use PHPUnit\Framework\TestCase;
final class ConditionLifecycleRegressionTest extends TestCase {
 public function testEncounterAdvanceTicksOutgoingCombatant():void {
  $s=(string)file_get_contents(dirname(__DIR__,5).'/app/Tabletop/Encounters/Services/EncounterManager.php');
  self::assertStringContainsString('$outgoing = $encounter->currentCombatant();',$s);
  self::assertStringContainsString('$this->conditionLifecycle->turnEnded(',$s);
 }
 public function testExpiryWritesBattleEvent():void {
  $s=(string)file_get_contents(dirname(__DIR__,5).'/app/Tabletop/Conditions/Services/ConditionLifecycle.php');
  self::assertStringContainsString("'condition-expired'",$s);
 }
}
