<?php
declare(strict_types=1);
namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Vision\Services;
use DateTimeImmutable;
use GreatMarketrealmTabletop\Tables\Scenes\Models\GridType;
use GreatMarketrealmTabletop\Tables\Scenes\Models\TableScene;
use GreatMarketrealmTabletop\Tables\Tokens\Models\TableToken;
use GreatMarketrealmTabletop\Tables\Tokens\Models\TableTokenType;
use GreatMarketrealmTabletop\Tables\Tokens\Models\TableTokenVisibility;
use GreatMarketrealmTabletop\Tabletop\Fog\Services\FogCellMapper;
use GreatMarketrealmTabletop\Tabletop\Vision\Models\VisionBarrier;
use PHPUnit\Framework\TestCase;
final class FogVisionBarrierIntegrationTest extends TestCase
{
    public function testFogMapperStopsAtWallAndPassesOpenDoor():void
    {
        $scene=TableScene::create('s','t','Map',1,400,400,GridType::SQUARE,40,new DateTimeImmutable());
        $token=TableToken::create('a','t','s','Auby',TableTokenType::CHARACTER,'x',1,.05,.05,1,1,TableTokenVisibility::VISIBLE,new DateTimeImmutable());
        $wall=new VisionBarrier('w','s',VisionBarrier::WALL,1,0,1,4);
        $closed=(new FogCellMapper())->visibleAround($scene,$token,[$wall]);
        self::assertContains('0:0',$closed);self::assertNotContains('1:0',$closed);
        $door=new VisionBarrier('d','s',VisionBarrier::DOOR,1,0,1,1);$door->toggleDoor();
        self::assertContains('1:0',(new FogCellMapper())->visibleAround($scene,$token,[$door]));
    }
}
