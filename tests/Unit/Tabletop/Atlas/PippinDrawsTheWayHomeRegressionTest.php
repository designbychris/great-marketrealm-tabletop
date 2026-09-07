<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Atlas;

use PHPUnit\Framework\TestCase;

final class PippinDrawsTheWayHomeRegressionTest extends TestCase
{
    private function source(string $path): string
    {
        return (string) file_get_contents(dirname(__DIR__, 4) . '/' . $path);
    }

    public function test_transition_repository_is_table_and_source_scene_scoped(): void
    {
        $php = $this->source('app/Tabletop/Atlas/Transitions/Repositories/WordPressSceneTransitionRepository.php');
        self::assertStringContainsString("private const OPTION = 'gmrt_scene_transitions';", $php);
        self::assertStringContainsString('$records[$tableId][$sourceSceneId]', $php);
    }

    public function test_keeper_can_link_only_two_distinct_scenes_on_the_same_table(): void
    {
        $php = $this->source('app/Tabletop/Http/KeepersAtlasAjaxController.php');
        self::assertStringContainsString('$sourceSceneId === $destinationSceneId', $php);
        self::assertStringContainsString('$this->scenes->find($tableId, $sourceSceneId)', $php);
        self::assertStringContainsString('$this->scenes->find($tableId, $destinationSceneId)', $php);
        self::assertStringContainsString('$this->assertDungeonMaster($tableId);', $php);
    }

    public function test_transition_records_destination_party_arrival_as_authoritative_anchor(): void
    {
        $php = $this->source('app/Tabletop/Http/KeepersAtlasAjaxController.php');
        self::assertStringContainsString("'destination_anchor' => 'party'", $php);
        self::assertStringContainsString("'keeper_controlled' => true", $php);
    }

    public function test_keeper_travel_activates_destination_through_existing_atlas_boundary(): void
    {
        $php = $this->source('app/Tabletop/Http/KeepersAtlasAjaxController.php');
        self::assertStringContainsString('$scene = $this->atlas->openMap($tableId, get_current_user_id(), $destinationSceneId);', $php);
    }

    public function test_atlas_ui_excludes_the_current_scene_from_destinations(): void
    {
        $view = $this->source('app/Tabletop/Views/chamber.php');
        self::assertStringContainsString('data-scene-transition-destination', $view);
        self::assertStringContainsString('$destinationId === (string) ($scene[\'id\'] ?? \'\')', $view);
    }

    public function test_route_controls_are_keeper_driven_and_explicit(): void
    {
        $view = $this->source('app/Tabletop/Views/chamber.php');
        self::assertStringContainsString('data-scene-transition-save', $view);
        self::assertStringContainsString('data-scene-transition-travel disabled', $view);
        self::assertStringContainsString('data-scene-transition-remove disabled', $view);
        self::assertStringContainsString('Keeper-controlled.', $view);
    }

    public function test_browser_loads_persisted_route_and_can_draw_erase_or_travel_it(): void
    {
        $js = $this->source('assets/js/tabletop.js');
        self::assertStringContainsString("request('gmrt_atlas_transition_status'", $js);
        self::assertStringContainsString("request('gmrt_atlas_link_transition'", $js);
        self::assertStringContainsString("request('gmrt_atlas_remove_transition'", $js);
        self::assertStringContainsString("request('gmrt_atlas_travel_transition'", $js);
    }

    public function test_provider_registers_all_transition_ajax_boundaries(): void
    {
        $php = $this->source('app/Tabletop/TabletopServiceProvider.php');
        self::assertStringContainsString("'wp_ajax_gmrt_atlas_transition_status'", $php);
        self::assertStringContainsString("'wp_ajax_gmrt_atlas_link_transition'", $php);
        self::assertStringContainsString("'wp_ajax_gmrt_atlas_remove_transition'", $php);
        self::assertStringContainsString("'wp_ajax_gmrt_atlas_travel_transition'", $php);
    }
}
