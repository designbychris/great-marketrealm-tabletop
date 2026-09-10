<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class TheForgeTellsAStoryRegressionTest extends TestCase
{
    private function root(string $path): string { return dirname(__DIR__, 5) . '/' . $path; }
    public function test_both_forge_surfaces_offer_story(): void { $v=file_get_contents($this->root('app/Tabletop/Views/chamber.php')); self::assertStringContainsString('data-atlas-forge-story',$v); self::assertStringContainsString('data-dungeon-forge-story',$v); }
    public function test_browser_sends_story_opt_in(): void { $j=file_get_contents($this->root('assets/js/tabletop.js')); self::assertStringContainsString('include_story',$j); }
    public function test_controller_persists_story_projection(): void { $c=file_get_contents($this->root('app/Tabletop/Http/DungeonForgeAjaxController.php')); self::assertStringContainsString("'story' => \$storyDraft",$c); self::assertStringContainsString("'include_story' => \$plan['include_story']",$c); }
    public function test_keeper_has_adventure_notes(): void { $v=file_get_contents($this->root('app/Tabletop/Views/chamber.php')); self::assertStringContainsString('Pippin’s Adventure Notes',$v); }
    public function test_player_ajax_does_not_receive_story(): void { $c=file_get_contents($this->root('app/Tabletop/Http/TabletopAjaxController.php')); self::assertStringContainsString("unset(\$forge['story'])",$c); }
    public function test_player_render_does_not_receive_story(): void { $v=file_get_contents($this->root('app/Tabletop/Views/chamber.php')); self::assertStringContainsString("unset(\$dungeonForge['story'])",$v); }
}
