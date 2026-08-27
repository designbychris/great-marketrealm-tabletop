<?php
declare(strict_types=1);
namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Vision\Models;
use GreatMarketrealmTabletop\Tabletop\Vision\Models\VisionBarrier;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
final class VisionBarrierTest extends TestCase
{
    public function testWallAlwaysBlocksSight():void{$b=new VisionBarrier('w','s',VisionBarrier::WALL,1,0,1,1,true);self::assertTrue($b->blocksSight());self::assertFalse($b->isOpen());}
    public function testClosedDoorCanBeOpened():void{$b=new VisionBarrier('d','s',VisionBarrier::DOOR,1,0,1,1);self::assertTrue($b->blocksSight());$b->toggleDoor();self::assertTrue($b->isOpen());self::assertFalse($b->blocksSight());}
    public function testWallCannotBeToggledLikeADoor():void{$this->expectException(InvalidArgumentException::class);(new VisionBarrier('w','s',VisionBarrier::WALL,1,0,1,1))->toggleDoor();}
}
