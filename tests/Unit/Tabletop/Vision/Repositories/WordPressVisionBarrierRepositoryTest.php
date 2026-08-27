<?php
declare(strict_types=1);
namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Vision\Repositories;
use GreatMarketrealmTabletop\Tabletop\Vision\Models\VisionBarrier;
use GreatMarketrealmTabletop\Tabletop\Vision\Repositories\WordPressVisionBarrierRepository;
use PHPUnit\Framework\TestCase;
final class WordPressVisionBarrierRepositoryTest extends TestCase
{
    protected function setUp():void{$GLOBALS['gmrt_test_options']=[];}
    public function testVisionLayerPersistsWithoutAutoload():void{$r=new WordPressVisionBarrierRepository();$r->save('t',new VisionBarrier('w','s',VisionBarrier::WALL,1,0,1,1));self::assertCount(1,$r->forScene('t','s'));self::assertFalse($GLOBALS['gmrt_test_options']['gmrt_vision_barriers']['autoload']);}
    public function testBarrierCanBeDeletedWithoutTouchingScene():void{$r=new WordPressVisionBarrierRepository();$r->save('t',new VisionBarrier('w','s',VisionBarrier::WALL,1,0,1,1));$r->delete('t','s','w');self::assertSame([],$r->forScene('t','s'));}
}
