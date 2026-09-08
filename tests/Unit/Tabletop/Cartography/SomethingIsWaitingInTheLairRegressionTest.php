<?php
declare(strict_types=1);
namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Cartography;
use PHPUnit\Framework\TestCase;
final class SomethingIsWaitingInTheLairRegressionTest extends TestCase
{
 private function s(string $p): string { return (string) file_get_contents(dirname(__DIR__,4).'/'.$p); }
 public function test_atlas_exposes_optional_lair_occupant(): void { $v=$this->s('app/Tabletop/Views/chamber.php'); self::assertStringContainsString('data-atlas-forge-lair-occupant-wrap',$v); self::assertStringContainsString('Something waiting in the lair',$v); }
 public function test_selector_reuses_bestiary(): void { $v=$this->s('app/Tabletop/Views/chamber.php'); self::assertStringContainsString('foreach ($bestiary as $creature)',$v); self::assertStringContainsString('Leave the lair empty',$v); }
 public function test_controls_require_selected_boss_lair(): void { $j=$this->s('assets/js/tabletop.js'); self::assertStringContainsString('const chosen = allowed && atlasForgeLair.checked;',$j); self::assertStringContainsString('atlasForgeLairOccupantWrap.hidden = !chosen',$j); }
 public function test_plan_carries_occupant_and_visibility(): void { $j=$this->s('assets/js/tabletop.js'); self::assertStringContainsString('plan.lair_occupant_id = atlasForgeLair?.checked',$j); self::assertStringContainsString('plan.lair_occupant_hidden = Boolean(',$j); }
 public function test_server_discards_occupant_without_semantic_lair(): void { $p=$this->s('app/Tabletop/Http/DungeonForgeAjaxController.php'); self::assertStringContainsString('$hasBossLair = false;',$p); self::assertStringContainsString('if (! $hasBossLair) $lairOccupantId = \'\';',$p); }
 public function test_server_validates_canonical_bestiary_record(): void { $p=$this->s('app/Tabletop/Http/DungeonForgeAjaxController.php'); self::assertStringContainsString('BestiaryRepositoryFactory::make()->find($lairOccupantId)',$p); self::assertStringContainsString('not recorded in the Keeper’s Bestiary',$p); }
 public function test_occupant_deploys_at_lair_centre_through_existing_boundary(): void { $p=$this->s('app/Tabletop/Http/DungeonForgeAjaxController.php'); self::assertStringContainsString('$lairX = max(0.0, min(1.0,',$p); self::assertStringContainsString('$lairY = max(0.0, min(1.0,',$p); self::assertStringContainsString('$this->bestiaryDeployment->deployAtPoint(',$p); }
 public function test_projection_remembers_lair_occupant_token(): void { $p=$this->s('app/Tabletop/Http/DungeonForgeAjaxController.php'); self::assertStringContainsString("'lair_occupant_token_id' => \$lairOccupantTokenId",$p); self::assertStringContainsString("'lair_occupant_hidden' => \$plan['lair_occupant_hidden']",$p); }
}
