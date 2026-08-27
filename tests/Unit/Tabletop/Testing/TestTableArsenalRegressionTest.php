<?php
declare(strict_types=1);
namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Testing;
use PHPUnit\Framework\TestCase;
final class TestTableArsenalRegressionTest extends TestCase
{
 public function testTrainingFixturesHaveTwoAttacksEach():void{$b=new \GreatMarketrealmTabletop\Tabletop\Testing\TestTableBlueprint();foreach($b->tokens(42) as $fixture){self::assertCount(2,$fixture['arsenal']);}}
 public function testAubyHasMeleeAndSpellAttacks():void{$b=new \GreatMarketrealmTabletop\Tabletop\Testing\TestTableBlueprint();$a=$b->tokens(42)[0]['arsenal'];self::assertSame('melee-weapon',$a[0]['kind']);self::assertSame('spell',$a[1]['kind']);}
 public function testProvisionerPersistsCombatArsenal():void{$s=(string)file_get_contents(dirname(__DIR__,4).'/app/Tabletop/Testing/TestTableProvisioner.php');self::assertStringContainsString('new WordPressCombatArsenalRepository()',$s);self::assertStringContainsString('$this->buildArsenal(',$s);}
}
