<?php
declare(strict_types=1);
namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Models;
use GreatMarketrealmTabletop\Tabletop\Models\TabletopChamberState;
use PHPUnit\Framework\TestCase;
final class TabletopChamberBattleLogStateTest extends TestCase
{
 public function testStateCanCarryBattleLog():void
 {
  $state=new TabletopChamberState([],[],[],null,[],null,[],[],[],[['round'=>1,'summary'=>'Auby hit Slime.']]);
  self::assertSame('Auby hit Slime.',$state->battleLog()[0]['summary']);
 }
}
