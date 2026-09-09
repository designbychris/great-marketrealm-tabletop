<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Cartography;

use GreatMarketrealmTabletop\Tabletop\Cartography\Services\ForgeSecretPlanner;
use PHPUnit\Framework\TestCase;

final class ForgeSecretPlannerTest extends TestCase
{
    public function test_secrets_are_opt_in(): void { self::assertSame([], (new ForgeSecretPlanner())->plan($this->plan(false))); }
    public function test_secrets_are_dungeon_only(): void { $p=$this->plan(true);$p['scene_type']='village';self::assertSame([], (new ForgeSecretPlanner())->plan($p)); }
    public function test_secret_plan_is_deterministic(): void { $p=$this->plan(true);$planner=new ForgeSecretPlanner();self::assertSame($planner->plan($p),$planner->plan($p)); }
    public function test_secret_vocabulary_starts_concealed_and_excludes_lair_cache(): void
    {
        $secrets=(new ForgeSecretPlanner())->plan($this->plan(true));
        self::assertSame(['secret-door','hidden-cache'],array_column($secrets,'kind'));
        self::assertFalse($secrets[0]['revealed']); self::assertFalse($secrets[1]['revealed']);
        self::assertSame(0,$secrets[1]['room_index']);
    }
    private function plan(bool $include):array{return ['include_secrets'=>$include,'scene_type'=>'dungeon','seed'=>'Pippin','cols'=>30,'rows'=>22,'doors'=>[['x1'=>.1,'y1'=>.2,'x2'=>.2,'y2'=>.2],['x1'=>.4,'y1'=>.5,'x2'=>.5,'y2'=>.5]],'rooms'=>[['x'=>2,'y'=>2,'w'=>8,'h'=>8,'role'=>'','boss_lair'=>false],['x'=>15,'y'=>8,'w'=>10,'h'=>10,'role'=>'lair','boss_lair'=>true]]];}
}
