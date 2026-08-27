<?php
declare(strict_types=1);
namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Arsenal;
use PHPUnit\Framework\TestCase;
final class CompanionArsenalBoundaryTest extends TestCase
{
 public function testCompanionBoundaryUsesOpaqueCharacterReference():void{$s=(string)file_get_contents(dirname(__DIR__,4).'/app/Tabletop/Arsenal/Contracts/CompanionArsenalSource.php');self::assertStringContainsString('string $opaqueCharacterReference',$s);self::assertStringContainsString('CombatArsenal',$s);}
 public function testTabletopDoesNotDependOnCompanionNamespace():void{$s=(string)file_get_contents(dirname(__DIR__,4).'/app/Tabletop/Arsenal/Contracts/CompanionArsenalSource.php');self::assertStringNotContainsString('GreatMarketrealmCompanion\\\\',$s);}
}
