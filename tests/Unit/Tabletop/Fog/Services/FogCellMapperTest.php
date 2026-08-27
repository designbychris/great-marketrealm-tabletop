<?php
declare(strict_types=1);
namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Fog\Services;
use DateTimeImmutable;
use GreatMarketrealmTabletop\Tabletop\Fog\Services\FogCellMapper;
use GreatMarketrealmTabletop\Tables\Scenes\Models\GridType;
use GreatMarketrealmTabletop\Tables\Scenes\Models\TableScene;
use GreatMarketrealmTabletop\Tables\Tokens\Models\TableToken;
use GreatMarketrealmTabletop\Tables\Tokens\Models\TableTokenType;
use GreatMarketrealmTabletop\Tables\Tokens\Models\TableTokenVisibility;
use PHPUnit\Framework\TestCase;
final class FogCellMapperTest extends TestCase
{
 public function testMapperHonoursCalibratedGridOffset():void
 {
  $s=TableScene::create('s','t','Map',1,400,400,GridType::SQUARE,40,new DateTimeImmutable());$s->calibrateGrid(40,0,-8,20,true);
  $cell=(new FogCellMapper())->cellFor($s,.5,.5);
  self::assertSame(5,$cell['column']);self::assertSame(5,$cell['row']);
 }
 public function testVisionRadiusCreatesSevenBySevenSquare():void
 {
  $s=TableScene::create('s','t','Map',1,400,400,GridType::SQUARE,40,new DateTimeImmutable());
  $token=TableToken::create('a','t','s','Auby',TableTokenType::CHARACTER,'x',1,.5,.5,1,1,TableTokenVisibility::VISIBLE,new DateTimeImmutable());
  self::assertCount(49,(new FogCellMapper())->visibleAround($s,$token));
 }
}
