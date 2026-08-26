<?php
declare(strict_types=1);
namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Testing;
use GreatMarketrealmTabletop\Tabletop\Testing\TestTableBlueprint;
use PHPUnit\Framework\TestCase;
final class TestTableBlueprintTest extends TestCase
{
 private TestTableBlueprint $b;
 protected function setUp():void{$this->b=new TestTableBlueprint();}
 public function testFixtureHasMemorableTableName():void{self::assertSame("Sage's Combat Testing Grounds",TestTableBlueprint::TABLE_NAME);}
 public function testFixtureIncludesTrainingSlime():void{self::assertContains('Training Slime',array_column($this->b->tokens(42),'label'));}
 public function testFixtureIncludesAuby():void{self::assertContains('Auby',array_column($this->b->tokens(42),'label'));}
 public function testAubyIsControlledByDungeonMaster():void{$x=$this->b->tokens(42);self::assertSame(42,$x[0]['controller']);self::assertSame('character',$x[0]['type']);}
 public function testFixtureHasFourCombatants():void{self::assertCount(4,$this->b->tokens(42));}
 public function testEveryFixtureHasCombatNumbers():void{foreach($this->b->tokens(42) as $x){self::assertGreaterThan(0,$x['hp']);self::assertGreaterThan(0,$x['ac']);self::assertCount(3,$x['damage']);}}
}
