<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Cartography;

use GreatMarketrealmTabletop\Tabletop\Cartography\Services\ForgeStoryPlanner;
use PHPUnit\Framework\TestCase;

final class ForgeStoryPlannerTest extends TestCase
{
    private function plan(array $extra = []): array
    {
        return $extra + ['scene_type'=>'dungeon','include_story'=>true,'seed'=>'Peppercorn-Story','theme'=>'pickle vault','rooms'=>[['x'=>1,'y'=>1,'w'=>6,'h'=>6,'role'=>'room'],['x'=>8,'y'=>1,'w'=>8,'h'=>8,'role'=>'lair','boss_lair'=>true]],'entry_anchor'=>['type'=>'entrance'],'lair_occupant_id'=>'kale-hydra'];
    }

    public function test_story_is_opt_in(): void { self::assertSame([], (new ForgeStoryPlanner())->plan($this->plan(['include_story'=>false]))); }
    public function test_story_is_dungeon_only(): void { self::assertSame([], (new ForgeStoryPlanner())->plan($this->plan(['scene_type'=>'forest']))); }
    public function test_story_is_deterministic(): void { $p=new ForgeStoryPlanner(); self::assertSame($p->plan($this->plan()),$p->plan($this->plan())); }
    public function test_story_has_title_hook_and_keeper_note(): void { $s=(new ForgeStoryPlanner())->plan($this->plan()); self::assertNotSame('', $s['title']); self::assertNotSame('', $s['hook']); self::assertNotSame('', $s['keeper_note']); }
    public function test_story_reflects_prepared_features(): void { $s=(new ForgeStoryPlanner())->plan($this->plan(),['secrets'=>[['id'=>'s']], 'traps'=>[['id'=>'t']], 'treasure'=>[['id'=>'g']], 'occupants'=>[['id'=>'o']]]); $text=json_encode($s); self::assertStringContainsString('Discovery',$text); self::assertStringContainsString('Complication',$text); self::assertStringContainsString('Reward',$text); self::assertStringContainsString('Pressure',$text); }
    public function test_boss_lair_creates_climax(): void { $s=(new ForgeStoryPlanner())->plan($this->plan()); self::assertStringContainsString('Climax',json_encode($s)); }
    public function test_summary_counts_semantic_parts(): void { $s=(new ForgeStoryPlanner())->plan($this->plan(),['traps'=>[['id'=>'a'],['id'=>'b']]]); self::assertStringContainsString('2 traps',$s['summary']); }
    public function test_sparse_dungeon_still_gets_exploration_beat(): void { $plan=$this->plan(['rooms'=>[['x'=>1,'y'=>1,'w'=>4,'h'=>4]],'entry_anchor'=>null,'lair_occupant_id'=>'']); $s=(new ForgeStoryPlanner())->plan($plan); self::assertGreaterThanOrEqual(2,count($s['beats'])); }
}
