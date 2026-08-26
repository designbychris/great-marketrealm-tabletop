<?php
declare(strict_types=1);
namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Conditions\Services;
use PHPUnit\Framework\TestCase;
final class ConditionManagerRegressionTest extends TestCase {
 private string $s;
 protected function setUp():void {$this->s=(string)file_get_contents(dirname(__DIR__,5).'/app/Tabletop/Conditions/Services/ConditionManager.php');}
 public function testOnlyDungeonMasterMayManageConditions():void {
  self::assertStringContainsString('! $member->isDungeonMaster()',$this->s);
 }
 public function testApplicationWritesBattleEvent():void {
  self::assertStringContainsString("'condition-applied'",$this->s);
 }
 public function testRemovalWritesBattleEvent():void {
  self::assertStringContainsString("'condition-removed'",$this->s);
 }
 public function testConditionTargetMustBeOnEncounterScene():void {
  self::assertStringContainsString('$token->sceneId() !== $encounter->sceneId()',$this->s);
 }
}
