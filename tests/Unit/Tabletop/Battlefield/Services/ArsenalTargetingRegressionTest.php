<?php
declare(strict_types=1);
namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Battlefield\Services;
use PHPUnit\Framework\TestCase;
final class ArsenalTargetingRegressionTest extends TestCase
{
 public function testTargetPreviewCanUseSelectedArsenalAttack():void{$s=(string)file_get_contents(dirname(__DIR__,5).'/app/Tabletop/Battlefield/Services/TargetingService.php');self::assertStringContainsString('$selectedAttack?->combat()',$s);self::assertStringContainsString("'attack_name' => \$selectedAttack?->name()",$s);}
}
