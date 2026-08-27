<?php
declare(strict_types=1);
namespace GreatMarketrealmTabletop\Tests\Unit\Tables\Scenes\Models;
use DateTimeImmutable;
use GreatMarketrealmTabletop\Tables\Scenes\Models\GridType;
use GreatMarketrealmTabletop\Tables\Scenes\Models\TableScene;
use PHPUnit\Framework\TestCase;
final class TableSceneGridReferenceTest extends TestCase
{
 public function testCalibrationPersistsReferenceViewportWidth():void
 {
  $s=TableScene::create('s','t','Map',1,1600,1000,GridType::SQUARE,35,new DateTimeImmutable());
  $s->calibrateGrid(35,13,8,20,true,1132);
  self::assertSame(1132,$s->gridReferenceWidth());
  self::assertSame(1132,$s->toArray()['grid_reference_width']);
 }
 public function testOldScenesDefaultToNoReferenceWidth():void
 {
  $s=TableScene::reconstitute(['id'=>'s','table_id'=>'t','name'=>'Map','map_attachment_id'=>1,'width'=>1600,'height'=>1000,'grid_type'=>'square','grid_size'=>35,'created_at'=>'2026-08-27T00:00:00+00:00']);
  self::assertSame(0,$s->gridReferenceWidth());
 }
}
