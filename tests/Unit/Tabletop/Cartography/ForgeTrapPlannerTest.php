<?php
declare(strict_types=1);
namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Cartography;
use GreatMarketrealmTabletop\Tabletop\Cartography\Services\ForgeTrapPlanner;
use PHPUnit\Framework\TestCase;
final class ForgeTrapPlannerTest extends TestCase
{
    public function test_traps_are_opt_in():void{self::assertSame([],(new ForgeTrapPlanner())->plan($this->plan(false)));}
    public function test_traps_are_dungeon_only():void{$p=$this->plan(true);$p['scene_type']='forest';self::assertSame([],(new ForgeTrapPlanner())->plan($p));}
    public function test_traps_are_deterministic():void{$p=$this->plan(true);$x=new ForgeTrapPlanner();self::assertSame($x->plan($p),$x->plan($p));}
    public function test_traps_begin_hidden_armed_and_unsprung():void{$traps=(new ForgeTrapPlanner())->plan($this->plan(true));self::assertNotEmpty($traps);foreach($traps as $trap){self::assertFalse($trap['revealed']);self::assertTrue($trap['armed']);self::assertFalse($trap['triggered']);}}
    public function test_boss_lair_is_not_selected_for_pressure_plate():void{$traps=(new ForgeTrapPlanner())->plan($this->plan(true));self::assertSame(0,$traps[0]['room_index']);}
    private function plan(bool $include):array{return['include_traps'=>$include,'scene_type'=>'dungeon','seed'=>'Pippin-click','cols'=>30,'rows'=>22,'doors'=>[['x1'=>.2,'y1'=>.2,'x2'=>.2,'y2'=>.3]],'rooms'=>[['x'=>2,'y'=>2,'w'=>8,'h'=>8,'role'=>'','boss_lair'=>false],['x'=>15,'y'=>8,'w'=>10,'h'=>10,'role'=>'lair','boss_lair'=>true]]];}
}
