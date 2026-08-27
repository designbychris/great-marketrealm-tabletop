<?php
declare(strict_types=1);
namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Fog\Models;
use GreatMarketrealmTabletop\Tabletop\Fog\Models\FogOfWarState;
use PHPUnit\Framework\TestCase;
final class FogOfWarStateTest extends TestCase
{
 public function testExplorationAccumulatesWithoutDuplicates():void{$s=new FogOfWarState('scene',true,['1:1']);$s->reveal(['1:1','2:2']);self::assertSame(['1:1','2:2'],$s->explored());}
 public function testExplorationMayBeClearedWithoutDisablingFog():void{$s=new FogOfWarState('scene',true,['1:1']);$s->clear();self::assertTrue($s->enabled());self::assertSame([],$s->explored());}
}
