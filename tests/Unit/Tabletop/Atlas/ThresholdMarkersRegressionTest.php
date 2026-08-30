<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Atlas;

use PHPUnit\Framework\TestCase;

final class ThresholdMarkersRegressionTest extends TestCase
{
    private function root(string $path): string
    {
        return dirname(__DIR__, 4) . '/' . $path;
    }

    public function test_thresholds_are_dm_only_in_the_chamber_projection(): void
    {
        $chamber = (string) file_get_contents($this->root('app/Tabletop/Services/TabletopChamber.php'));
        $state = (string) file_get_contents($this->root('app/Tabletop/Models/TabletopChamberState.php'));
        self::assertStringContainsString('$viewer->isDungeonMaster() && $activeScene !== null && $this->thresholds !== null', $chamber);
        self::assertStringContainsString('public function thresholds(): array', $state);
    }

    public function test_keeper_can_place_party_and_monster_thresholds_on_the_map(): void
    {
        $view = (string) file_get_contents($this->root('app/Tabletop/Views/chamber.php'));
        $js = (string) file_get_contents($this->root('assets/js/tabletop.js'));
        self::assertStringContainsString('data-threshold-place="party"', $view);
        self::assertStringContainsString('data-threshold-place="monster"', $view);
        self::assertStringContainsString("request('gmrt_atlas_place_threshold'", $js);
    }

    public function test_threshold_mutations_are_server_side_dm_guarded(): void
    {
        $manager = (string) file_get_contents($this->root('app/Tabletop/Atlas/Thresholds/Services/ThresholdManager.php'));
        $provider = (string) file_get_contents($this->root('app/Tabletop/TabletopServiceProvider.php'));
        self::assertStringContainsString('$this->assertDungeonMaster($tableId, $viewerUserId);', $manager);
        self::assertStringContainsString('wp_ajax_gmrt_atlas_place_threshold', $provider);
        self::assertStringContainsString('wp_ajax_gmrt_atlas_remove_threshold', $provider);
    }

    public function test_opening_a_scene_welcomes_only_missing_party_characters(): void
    {
        $atlas = (string) file_get_contents($this->root('app/Tabletop/Atlas/Services/KeepersAtlas.php'));
        $manager = (string) file_get_contents($this->root('app/Tabletop/Atlas/Thresholds/Services/ThresholdManager.php'));
        self::assertStringContainsString('$this->thresholds->welcomeParty($tableId, $scene);', $atlas);
        self::assertStringContainsString('$alreadyPresent = false;', $manager);
        self::assertStringContainsString('if ($alreadyPresent)', $manager);
        self::assertStringContainsString('TableMemberRole::PLAYER', $manager);
        self::assertStringContainsString('TableMemberStatus::ACTIVE', $manager);
    }

    public function test_existing_scene_owned_character_positions_win_over_thresholds(): void
    {
        $manager = (string) file_get_contents($this->root('app/Tabletop/Atlas/Thresholds/Services/ThresholdManager.php'));
        self::assertStringContainsString('$this->tokens->forScene($tableId, $scene->id())', $manager);
        self::assertStringContainsString('$token->controllerUserId() === $member->userId()', $manager);
        self::assertStringContainsString('(string) ($token->sourceReference() ?? \'\') === $characterId', $manager);
        self::assertStringNotContainsString('->move(', $manager);
    }

    public function test_scene_deletion_also_clears_threshold_markers(): void
    {
        $cleaner = (string) file_get_contents($this->root('app/Tabletop/Atlas/Services/SceneShelfCleaner.php'));
        self::assertStringContainsString("'gmrt_scene_thresholds'", $cleaner);
    }

    public function test_roadmap_preserves_bestiary_before_cartography_assistant(): void
    {
        $roadmap = (string) file_get_contents($this->root('ROADMAP.md'));
        $phase = (string) file_get_contents($this->root('docs/Roadmap/PHASE-IV.28D.md'));
        self::assertStringContainsString('[x] **IV.28D — The Threshold Markers**', $roadmap);
        self::assertStringContainsString("[ ] **IV.29 — The Keeper's Bestiary**", $roadmap);
        self::assertLessThan(
            strpos($roadmap, "Keeper's Cartography Assistant"),
            strpos($roadmap, "IV.29 — The Keeper's Bestiary")
        );
        self::assertStringContainsString('A character token already present in the destination Scene is never moved', $phase);
    }
}
