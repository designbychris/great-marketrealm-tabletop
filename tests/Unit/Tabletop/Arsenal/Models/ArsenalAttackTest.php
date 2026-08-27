<?php
declare(strict_types=1);
namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Arsenal\Models;
use GreatMarketrealmTabletop\Tabletop\Arsenal\Models\ArsenalAttack;
use GreatMarketrealmTabletop\Tabletop\Arsenal\Models\AttackKind;
use GreatMarketrealmTabletop\Tabletop\Battle\Models\CombatProfile;
use GreatMarketrealmTabletop\Tabletop\Battle\Models\DamageProfile;
use PHPUnit\Framework\TestCase;
final class ArsenalAttackTest extends TestCase
{
 public function testAttackRoundTrips():void{$a=new ArsenalAttack('staff','t','Keeper Staff',AttackKind::MELEE_WEAPON,new CombatProfile('t',10,5,5,5),new DamageProfile('t',1,6,3,'bludgeoning'),['versatile'],'companion','opaque');self::assertSame($a->toArray(),ArsenalAttack::reconstitute($a->toArray())->toArray());}
 public function testAttackKeepsCompanionSourceReference():void{$a=new ArsenalAttack('spark','t','Spark',AttackKind::SPELL,new CombatProfile('t',10,6,60,60),new DamageProfile('t',1,8,0,'radiant'),[],'companion','opaque-123');self::assertSame('companion',$a->sourceType());self::assertSame('opaque-123',$a->sourceReference());}
}
