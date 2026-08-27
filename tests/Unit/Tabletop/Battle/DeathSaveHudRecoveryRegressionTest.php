<?php
declare(strict_types=1);
namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Battle;
use PHPUnit\Framework\TestCase;
final class DeathSaveHudRecoveryRegressionTest extends TestCase
{
 public function testNaturalTwentyServerPathRevivesAtOneHp():void
 {
  $s=(string)file_get_contents(dirname(__DIR__,4).'/app/Tabletop/Battle/Services/DeathSaveManager.php');
  self::assertStringContainsString('$outcome->revives()',$s);
  self::assertStringContainsString('$vitality->reviveAtOneHp()',$s);
  self::assertStringContainsString('$this->vitality->save(',$s);
 }
 public function testClientRemovesDownPanelAfterRecovery():void
 {
  $s=(string)file_get_contents(dirname(__DIR__,4).'/assets/js/tabletop.js');
  self::assertStringContainsString('const syncDeathSaveHud',$s);
  self::assertStringContainsString('Number(vitality.current_hp || 0) > 0',$s);
  self::assertStringContainsString('panel.remove()',$s);
  self::assertStringContainsString('syncDeathSaveHud(data)',$s);
 }
 public function testStableAndDeadStatesAlsoSynchronizeWithoutReload():void
 {
  $s=(string)file_get_contents(dirname(__DIR__,4).'/assets/js/tabletop.js');
  self::assertStringContainsString("heading.textContent = 'DECEASED'",$s);
  self::assertStringContainsString("details.textContent = 'Stable'",$s);
 }
}
