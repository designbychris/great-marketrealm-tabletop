<?php
declare(strict_types=1);
namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Fog\Repositories;
use GreatMarketrealmTabletop\Tabletop\Fog\Models\FogOfWarState;
use GreatMarketrealmTabletop\Tabletop\Fog\Repositories\WordPressFogOfWarRepository;
use PHPUnit\Framework\TestCase;
final class WordPressFogOfWarRepositoryTest extends TestCase
{
 protected function setUp():void{$GLOBALS['gmrt_test_options']=[];}
 public function testFogPersistsWithoutAutoload():void{$r=new WordPressFogOfWarRepository();$s=new FogOfWarState('scene',true,['1:1']);$r->save('table',$s);self::assertSame(['1:1'],$r->forScene('table','scene')->explored());self::assertFalse($GLOBALS['gmrt_test_options']['gmrt_fog_of_war']['autoload']);}
}
