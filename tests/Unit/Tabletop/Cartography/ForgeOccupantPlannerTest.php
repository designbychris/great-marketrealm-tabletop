<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Cartography;

use GreatMarketrealmTabletop\Tabletop\Bestiary\Contracts\BestiaryRepository;
use GreatMarketrealmTabletop\Tabletop\Bestiary\Models\BestiaryCreature;
use GreatMarketrealmTabletop\Tabletop\Cartography\Services\ForgeOccupantPlanner;
use PHPUnit\Framework\TestCase;

final class ForgeOccupantPlannerTest extends TestCase
{
    public function test_population_is_opt_in(): void { self::assertSame([], $this->planner()->plan($this->plan(false))); }
    public function test_only_dungeons_are_populated(): void { $p=$this->plan(true);$p['scene_type']='forest';self::assertSame([], $this->planner()->plan($p)); }
    public function test_population_is_deterministic(): void { $p=$this->plan(true);self::assertSame($this->planner()->plan($p),$this->planner()->plan($p)); }
    public function test_boss_lair_is_never_used_for_ordinary_population(): void { $p=$this->plan(true);$p['rooms']=[['x'=>2,'y'=>2,'w'=>10,'h'=>10,'role'=>'lair','boss_lair'=>true]];self::assertSame([], $this->planner()->plan($p)); }
    public function test_boss_definition_is_excluded_from_ordinary_pool(): void { $p=$this->plan(true);$p['lair_occupant_id']='only';$planner=new ForgeOccupantPlanner(new OccupantBestiary([$this->creature('only')]));self::assertSame([], $planner->plan($p)); }

    private function planner(): ForgeOccupantPlanner { return new ForgeOccupantPlanner(new OccupantBestiary([$this->creature('a'),$this->creature('b')])); }
    private function creature(string $id): BestiaryCreature { return new BestiaryCreature($id,strtoupper($id),'beast','medium',12,20,30); }
    private function plan(bool $populate): array { return ['populate_rooms'=>$populate,'scene_type'=>'dungeon','seed'=>'Pippin','cols'=>30,'rows'=>22,'rooms'=>array_fill(0,12,['x'=>2,'y'=>2,'w'=>8,'h'=>8,'role'=>'','boss_lair'=>false])]; }
}
final class OccupantBestiary implements BestiaryRepository { public function __construct(private array $records){} public function all():array{return $this->records;} public function find(string $id):?BestiaryCreature{foreach($this->records as $r)if($r->id()===$id)return $r;return null;} }
