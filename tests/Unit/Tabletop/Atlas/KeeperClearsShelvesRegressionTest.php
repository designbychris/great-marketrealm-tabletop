<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Atlas;

use PHPUnit\Framework\TestCase;

final class KeeperClearsShelvesRegressionTest extends TestCase
{
    private string $root;
    protected function setUp(): void { $this->root = dirname(__DIR__, 4); }

    public function test_atlas_refuses_to_delete_live_or_final_scene(): void
    {
        $source = file_get_contents($this->root . '/app/Tabletop/Atlas/Services/KeepersAtlas.php');
        self::assertStringContainsString('count($scenes) <= 1', $source);
        self::assertStringContainsString('$scene->isActive()', $source);
        self::assertStringContainsString('The live Scene cannot be removed.', $source);
    }

    public function test_scene_shelf_cleaner_removes_scene_and_token_owned_state(): void
    {
        $source = file_get_contents($this->root . '/app/Tabletop/Atlas/Services/SceneShelfCleaner.php');
        foreach (['gmrt_table_tokens','gmrt_table_encounters','gmrt_fog_of_war','gmrt_vision_barriers','gmrt_footstep_trails','gmrt_carried_lights','gmrt_dropped_lights','gmrt_magical_lights','gmrt_token_vitality','gmrt_death_saves','gmrt_token_conditions','gmrt_battle_events'] as $option) {
            self::assertStringContainsString($option, $source);
        }
    }

    public function test_scene_record_is_removed_by_the_same_authoritative_cleaner(): void
    {
        $source = file_get_contents($this->root . '/app/Tabletop/Atlas/Services/SceneShelfCleaner.php');
        self::assertStringContainsString('forgetScene(\'gmrt_table_scenes\', $tableId, $sceneId)', $source);
    }

    public function test_atlas_drawer_requires_named_confirmation_for_removal(): void
    {
        $view = file_get_contents($this->root . '/app/Tabletop/Views/chamber.php');
        $js = file_get_contents($this->root . '/assets/js/tabletop.js');
        self::assertStringContainsString('data-atlas-delete-map', $view);
        self::assertStringContainsString('data-scene-name=', $view);
        self::assertStringContainsString('Permanently delete', $js);
        self::assertStringContainsString('gmrt_atlas_delete_map', $js);
    }

    public function test_delete_action_is_registered_and_dm_guarded(): void
    {
        $provider = file_get_contents($this->root . '/app/Tabletop/TabletopServiceProvider.php');
        $atlas = file_get_contents($this->root . '/app/Tabletop/Atlas/Services/KeepersAtlas.php');
        self::assertStringContainsString('wp_ajax_gmrt_atlas_delete_map', $provider);
        self::assertStringContainsString('$this->assertDungeonMaster($tableId, $viewerUserId);', $atlas);
    }
}
