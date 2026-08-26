<?php
declare(strict_types=1);
namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Testing;
use PHPUnit\Framework\TestCase;
final class TestTableProvisionerRegressionTest extends TestCase
{
 private string $s;
 protected function setUp():void{$this->s=(string)file_get_contents(dirname(__DIR__,4).'/app/Tabletop/Testing/TestTableProvisioner.php');}
 public function testProvisionerUsesRealDomainFactories():void{foreach(['TableRegistryFactory::make()','TableSceneManagerFactory::make()','TableTokenManagerFactory::make()','EncounterManagerFactory::make()'] as $x){self::assertStringContainsString($x,$this->s);}}
 public function testProvisionerIsIdempotentForExistingOpenFixture():void{self::assertStringContainsString('TestTableBlueprint::TABLE_NAME',$this->s);self::assertStringContainsString("\$existing->status()!=='ended'",$this->s);}

 public function testExistingFixtureReceivesLatestCombatProfiles():void
 {
     self::assertStringContainsString(
         '$this->syncCombatProfiles(',
         $this->s
     );
     self::assertStringContainsString(
         'new WordPressDamageDefenseRepository()',
         $this->s
     );
 }

}
