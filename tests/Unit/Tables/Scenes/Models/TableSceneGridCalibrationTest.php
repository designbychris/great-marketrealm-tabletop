<?php
declare(strict_types=1);
namespace GreatMarketrealmTabletop\Tests\Unit\Tables\Scenes\Models;
use DateTimeImmutable;
use GreatMarketrealmTabletop\Tables\Scenes\Models\GridType;
use GreatMarketrealmTabletop\Tables\Scenes\Models\TableScene;
use PHPUnit\Framework\TestCase;
final class TableSceneGridCalibrationTest extends TestCase {
 public function testGridCalibrationPersistsVisualSettings():void { $s=TableScene::create('s','t','Map',1,1000,800,GridType::SQUARE,64,new DateTimeImmutable()); $s->calibrateGrid(70,-3,11,35,false); $a=$s->toArray(); self::assertSame(70,$a['grid_size']); self::assertSame(-3,$a['grid_offset_x']); self::assertSame(11,$a['grid_offset_y']); self::assertSame(35,$a['grid_opacity']); self::assertFalse($a['grid_visible']); }
 public function testOldSceneRecordsReceiveSafeGridDefaults():void { $s=TableScene::reconstitute(['id'=>'s','table_id'=>'t','name'=>'Map','map_attachment_id'=>1,'width'=>1000,'height'=>800,'grid_type'=>'square','grid_size'=>64,'created_at'=>'2026-08-27T00:00:00+00:00']); self::assertSame(0,$s->gridOffsetX()); self::assertSame(0,$s->gridOffsetY()); self::assertSame(13,$s->gridOpacity()); self::assertTrue($s->gridVisible()); }
}
