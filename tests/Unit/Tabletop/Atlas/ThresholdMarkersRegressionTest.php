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
        self::assertStringContainsString("? 'gmrt_atlas_move_threshold'", $js);
        self::assertStringContainsString(": 'gmrt_atlas_place_threshold'", $js);
        self::assertStringContainsString("request(action,", $js);
    }

    public function test_threshold_placement_owns_the_next_map_click_and_stays_visible(): void
    {
        $js = (string) file_get_contents($this->root('assets/js/tabletop.js'));
        $css = (string) file_get_contents($this->root('assets/css/tabletop.css'));

        self::assertStringContainsString("board.addEventListener('click', async (event) => {", $js);
        self::assertStringContainsString('event.stopImmediatePropagation();', $js);
        self::assertStringContainsString("}, true);", $js);
        self::assertStringContainsString('data-threshold-placement-notice', $js);
        self::assertStringContainsString("board.classList.add('is-threshold-placing')", $js);
        self::assertStringContainsString('.gmrt-board__viewport.is-threshold-placing', $css);
        self::assertStringContainsString('cursor: crosshair !important;', $css);
    }

    public function test_threshold_mode_blocks_map_panning_and_cancel_is_immediate(): void
    {
        $js = (string) file_get_contents($this->root('assets/js/tabletop.js'));
        self::assertStringContainsString('|| thresholdPlacement', $js);
        self::assertStringContainsString("cancel.addEventListener('pointerdown', cancelPlacement);", $js);
        self::assertStringContainsString("cancel.addEventListener('click', cancelPlacement);", $js);
        self::assertStringContainsString('clearThresholdPlacement();', $js);
    }

    public function test_threshold_mutations_are_server_side_dm_guarded(): void
    {
        $manager = (string) file_get_contents($this->root('app/Tabletop/Atlas/Thresholds/Services/ThresholdManager.php'));
        $provider = (string) file_get_contents($this->root('app/Tabletop/TabletopServiceProvider.php'));
        self::assertStringContainsString('$this->assertDungeonMaster($tableId, $viewerUserId);', $manager);
        self::assertStringContainsString('wp_ajax_gmrt_atlas_place_threshold', $provider);
        self::assertStringContainsString('wp_ajax_gmrt_atlas_remove_threshold', $provider);
    }

    public function test_opening_a_scene_does_not_copy_the_party_into_it(): void
    {
        $atlas = (string) file_get_contents($this->root('app/Tabletop/Atlas/Services/KeepersAtlas.php'));
        self::assertStringContainsString('return $this->scenes->activate($tableId, $sceneId);', $atlas);
        self::assertStringNotContainsString('welcomeParty($tableId, $scene)', $atlas);
    }

    public function test_each_player_crosses_their_own_threshold_during_live_passage(): void
    {
        $manager = (string) file_get_contents($this->root('app/Tabletop/Atlas/Thresholds/Services/ThresholdManager.php'));
        $provider = (string) file_get_contents($this->root('app/Tabletop/TabletopServiceProvider.php'));
        $js = (string) file_get_contents($this->root('assets/js/tabletop.js'));

        self::assertStringContainsString('public function welcomeAdventurer(', $manager);
        self::assertStringContainsString('$this->members->find($tableId, $viewerUserId)', $manager);
        self::assertStringContainsString('$token->controllerUserId() === $viewerUserId', $manager);
        self::assertStringContainsString('wp_ajax_gmrt_atlas_arrive_at_threshold', $provider);
        self::assertStringContainsString("request('gmrt_atlas_arrive_at_threshold'", $js);
        self::assertStringContainsString("root.dataset.viewerRole === 'player'", $js);
    }

    public function test_existing_scene_owned_character_positions_win_over_thresholds(): void
    {
        $manager = (string) file_get_contents($this->root('app/Tabletop/Atlas/Thresholds/Services/ThresholdManager.php'));
        self::assertStringContainsString('$this->tokens->forScene($tableId, $scene->id())', $manager);
        self::assertStringContainsString('$token->controllerUserId() === $viewerUserId', $manager);
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
        self::assertStringContainsString("[x] **IV.29 — The Keeper's Bestiary**", $roadmap);
        self::assertLessThan(
            strpos($roadmap, "Keeper's Cartography Assistant"),
            strpos($roadmap, "IV.29 — The Keeper's Bestiary")
        );
        self::assertStringContainsString('A character token already present in the destination Scene is never moved', $phase);
    }
    public function test_threshold_coordinates_follow_the_battlemap_not_the_viewport_shell(): void
    {
        $js = (string) file_get_contents($this->root('assets/js/tabletop.js'));
        self::assertStringContainsString("board.querySelector('[data-battlemap-image]')", $js);
        self::assertStringContainsString('const rect = (battlemap || board).getBoundingClientRect();', $js);
    }

    public function test_multiple_thresholds_are_stored_by_marker_id_without_replacing_their_siblings(): void
    {
        $repository = (string) file_get_contents($this->root('app/Tabletop/Atlas/Thresholds/Repositories/WordPressThresholdRepository.php'));
        self::assertStringContainsString('$records[$marker->tableId()][$marker->sceneId()][$marker->id()] = $marker->toArray();', $repository);
        self::assertStringContainsString('foreach ($this->records()[$tableId][$sceneId] ?? [] as $record)', $repository);
    }

    public function test_keeper_can_reposition_a_threshold_without_forging_a_second_marker(): void
    {
        $manager = (string) file_get_contents($this->root('app/Tabletop/Atlas/Thresholds/Services/ThresholdManager.php'));
        $provider = (string) file_get_contents($this->root('app/Tabletop/TabletopServiceProvider.php'));
        $js = (string) file_get_contents($this->root('assets/js/tabletop.js'));

        self::assertStringContainsString('public function move(', $manager);
        self::assertStringContainsString('$existing->id()', $manager);
        self::assertStringContainsString('wp_ajax_gmrt_atlas_move_threshold', $provider);
        self::assertStringContainsString("? 'gmrt_atlas_move_threshold'", $js);
    }

    public function test_threshold_markers_offer_reposition_and_explicit_shift_remove(): void
    {
        $view = (string) file_get_contents($this->root('app/Tabletop/Views/chamber.php'));
        $js = (string) file_get_contents($this->root('assets/js/tabletop.js'));

        self::assertStringContainsString('data-threshold-marker=', $view);
        self::assertStringContainsString('data-threshold-type=', $view);
        self::assertStringContainsString('Click to reposition; Shift-click to remove.', $view);
        self::assertStringContainsString('if (event.shiftKey)', $js);
    }

}
