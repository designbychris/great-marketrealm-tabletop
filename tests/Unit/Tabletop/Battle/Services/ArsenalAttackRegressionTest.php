<?php
declare(strict_types=1);
namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Battle\Services;
use PHPUnit\Framework\TestCase;
final class ArsenalAttackRegressionTest extends TestCase
{
 private function source():string{return (string)file_get_contents(dirname(__DIR__,5).'/app/Tabletop/Battle/Services/AttackManager.php');}
 public function testSelectedAttackSuppliesCombatAndDamageProfiles():void{$s=$this->source();self::assertStringContainsString('$selectedAttack?->combat()',$s);self::assertStringContainsString('$selectedAttack?->damage()',$s);}
 public function testUnknownSelectedAttackIsDenied():void{self::assertStringContainsString('The selected attack is not in this combatant Arsenal.',$this->source());}
 public function testLegacyProfilesRemainFallback():void{$s=$this->source();self::assertStringContainsString('?? $this->profiles->forToken',$s);self::assertStringContainsString('?? $this->damageProfiles->forToken',$s);}
 public function testAttackEventRecordsArsenalIdentity():void{self::assertStringContainsString("'attack_name' => \$selectedAttack?->name()",$this->source());}
}
