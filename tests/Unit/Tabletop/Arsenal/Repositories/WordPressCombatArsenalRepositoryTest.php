<?php
declare(strict_types=1);
namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Arsenal\Repositories;
use GreatMarketrealmTabletop\Tabletop\Arsenal\Models\ArsenalAttack;
use GreatMarketrealmTabletop\Tabletop\Arsenal\Models\AttackKind;
use GreatMarketrealmTabletop\Tabletop\Arsenal\Models\CombatArsenal;
use GreatMarketrealmTabletop\Tabletop\Arsenal\Repositories\WordPressCombatArsenalRepository;
use GreatMarketrealmTabletop\Tabletop\Battle\Models\CombatProfile;
use GreatMarketrealmTabletop\Tabletop\Battle\Models\DamageProfile;
use PHPUnit\Framework\TestCase;
final class WordPressCombatArsenalRepositoryTest extends TestCase
{
 protected function setUp():void{$GLOBALS['gmrt_test_options']=[];}
 public function testArsenalPersistsWithoutAutoload():void{$r=new WordPressCombatArsenalRepository();$a=new ArsenalAttack('staff','t','Staff',AttackKind::MELEE_WEAPON,new CombatProfile('t'),new DamageProfile('t'));$r->save('table',new CombatArsenal('t',[$a]));self::assertSame('Staff',$r->forToken('table','t')->find('staff')?->name());self::assertFalse($GLOBALS['gmrt_test_options']['gmrt_combat_arsenals']['autoload']);}
 public function testMissingArsenalIsEmpty():void{self::assertSame([],(new WordPressCombatArsenalRepository())->forToken('table','t')->attacks());}
}
