<?php
declare(strict_types=1);
namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Presentation;
use PHPUnit\Framework\TestCase;
final class ArsenalHudRegressionTest extends TestCase
{
 public function testHudOffersArsenalAttackSelector():void{$s=(string)file_get_contents(dirname(__DIR__,4).'/app/Tabletop/Views/chamber.php');self::assertStringContainsString('data-arsenal-attack',$s);self::assertStringContainsString("'damage_type'",$s);}
 public function testClientSendsAttackIdToPreviewAndAttack():void{$s=(string)file_get_contents(dirname(__DIR__,4).'/assets/js/tabletop.js');self::assertGreaterThanOrEqual(2,substr_count($s,'attack_id: arsenalAttack'));}
 public function testDiceworksNamesSelectedAttack():void{$s=(string)file_get_contents(dirname(__DIR__,4).'/assets/js/tabletop.js');self::assertStringContainsString('String(selectedAttack.name)',$s);}
 public function testBattlefieldViewportClipsHorizontalOverflow():void{$s=(string)file_get_contents(dirname(__DIR__,4).'/assets/css/tabletop.css');self::assertStringContainsString(".gmrt-board__viewport {\n    position: relative;\n    overflow: hidden;",$s);}
}
