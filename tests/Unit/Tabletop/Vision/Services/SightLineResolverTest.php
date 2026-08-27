<?php
declare(strict_types=1);
namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Vision\Services;
use GreatMarketrealmTabletop\Tabletop\Vision\Models\VisionBarrier;
use GreatMarketrealmTabletop\Tabletop\Vision\Services\SightLineResolver;
use PHPUnit\Framework\TestCase;
final class SightLineResolverTest extends TestCase
{
    public function testWallBetweenAdjacentCellsBlocksSight():void{$wall=new VisionBarrier('w','s',VisionBarrier::WALL,1,0,1,1);self::assertFalse((new SightLineResolver())->canSee(0,0,1,0,[$wall]));}
    public function testOpenDoorPermitsSight():void{$door=new VisionBarrier('d','s',VisionBarrier::DOOR,1,0,1,1);$door->toggleDoor();self::assertTrue((new SightLineResolver())->canSee(0,0,1,0,[$door]));}
    public function testBarrierAwayFromRayDoesNotBlockSight():void{$wall=new VisionBarrier('w','s',VisionBarrier::WALL,5,5,5,6);self::assertTrue((new SightLineResolver())->canSee(0,0,2,0,[$wall]));}
}
