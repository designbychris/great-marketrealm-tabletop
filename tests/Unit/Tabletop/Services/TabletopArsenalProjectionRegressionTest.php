<?php
declare(strict_types=1);
namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Services;
use PHPUnit\Framework\TestCase;
final class TabletopArsenalProjectionRegressionTest extends TestCase
{
 public function testChamberProjectsArsenalForVisibleTokens():void
 {
  $s=(string)file_get_contents(dirname(__DIR__,4).'/app/Tabletop/Services/TabletopChamber.php');

  self::assertMatchesRegularExpression(
   '/\$this->arsenals\s*->forToken\s*\(/s',
   $s
  );
  self::assertStringContainsString(
   '$arsenals[$tokenId]',
   $s
  );
 }
 public function testStateEndpointExposesArsenals():void{$s=(string)file_get_contents(dirname(__DIR__,4).'/app/Tabletop/Http/TabletopAjaxController.php');self::assertStringContainsString("'arsenals' => \$state->arsenals()",$s);}
}
