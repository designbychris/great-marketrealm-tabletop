<?php
declare(strict_types=1);
namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Arsenal\Models;
use GreatMarketrealmTabletop\Tabletop\Arsenal\Models\ArsenalAttack;
use GreatMarketrealmTabletop\Tabletop\Arsenal\Models\AttackKind;
use GreatMarketrealmTabletop\Tabletop\Arsenal\Models\CombatArsenal;
use GreatMarketrealmTabletop\Tabletop\Battle\Models\CombatProfile;
use GreatMarketrealmTabletop\Tabletop\Battle\Models\DamageProfile;
use PHPUnit\Framework\TestCase;
final class CombatArsenalTest extends TestCase
{
 private function attack(string $id):ArsenalAttack{return new ArsenalAttack($id,'t',$id,AttackKind::MELEE_WEAPON,new CombatProfile('t'),new DamageProfile('t'));}
 public function testArsenalFindsAttackById():void{$a=$this->attack('staff');$arsenal=new CombatArsenal('t',[$a]);self::assertSame($a,$arsenal->find('staff'));self::assertNull($arsenal->find('missing'));}
 public function testArsenalSerializesAllAttacks():void{$arsenal=new CombatArsenal('t',[$this->attack('a'),$this->attack('b')]);self::assertCount(2,$arsenal->toArray()['attacks']);}
}
