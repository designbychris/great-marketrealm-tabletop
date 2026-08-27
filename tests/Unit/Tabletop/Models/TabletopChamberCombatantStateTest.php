<?php
declare(strict_types=1);
namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Models;
use GreatMarketrealmTabletop\Tabletop\Models\TabletopChamberState;
use PHPUnit\Framework\TestCase;
final class TabletopChamberCombatantStateTest extends TestCase
{
 public function testStateCanCarryCombatantStates():void
 {
  $state=new TabletopChamberState([],[],[],null,[],null,[],[],[],[],['token-a'=>'defeated']);
  self::assertSame('defeated',$state->combatantStates()['token-a']);
 }
}
