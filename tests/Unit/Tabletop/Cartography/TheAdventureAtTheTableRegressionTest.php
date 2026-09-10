<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class TheAdventureAtTheTableRegressionTest extends TestCase
{
    private function root(string $path): string
    {
        return dirname(__DIR__, 4) . '/' . ltrim($path, '/');
    }

    public function test_keeper_story_beats_have_persistent_run_controls(): void
    {
        $view = (string) file_get_contents($this->root('app/Tabletop/Views/chamber.php'));

        self::assertStringContainsString('data-story-beat-roster', $view);
        self::assertStringContainsString('data-story-beat-action="activate"', $view);
        self::assertStringContainsString('data-story-beat-action="resolve"', $view);
        self::assertStringContainsString('data-story-beat-action="reset"', $view);
    }

    public function test_old_story_beats_without_status_remain_pending(): void
    {
        $view = (string) file_get_contents($this->root('app/Tabletop/Views/chamber.php'));

        self::assertStringContainsString("\$beat['status'] ?? 'pending'", $view);
        self::assertStringContainsString("['pending', 'active', 'resolved']", $view);
    }

    public function test_browser_posts_story_beat_actions_to_the_forge_boundary(): void
    {
        $js = (string) file_get_contents($this->root('assets/js/tabletop.js'));

        self::assertStringContainsString("request('gmrt_forge_story_beat_action'", $js);
        self::assertStringContainsString('beat_index: beatIndex', $js);
        self::assertStringContainsString('story_action: action', $js);
    }

    public function test_keeper_endpoint_is_registered(): void
    {
        $provider = (string) file_get_contents($this->root('app/Tabletop/TabletopServiceProvider.php'));

        self::assertStringContainsString('wp_ajax_gmrt_forge_story_beat_action', $provider);
        self::assertStringContainsString("[$this->dungeonForgeAjax, 'storyBeatAction']", $provider);
    }

    public function test_story_beat_actions_mutate_the_existing_forge_projection(): void
    {
        $controller = (string) file_get_contents($this->root('app/Tabletop/Http/DungeonForgeAjaxController.php'));

        self::assertStringContainsString('public function storyBeatAction(): void', $controller);
        self::assertStringContainsString("\$projection['story'] = \$story;", $controller);
        self::assertStringContainsString('$this->forge->save($tableId, $sceneId, $projection);', $controller);
    }

    public function test_only_one_story_beat_is_current_at_a_time(): void
    {
        $controller = (string) file_get_contents($this->root('app/Tabletop/Http/DungeonForgeAjaxController.php'));

        self::assertStringContainsString("=== 'active'", $controller);
        self::assertStringContainsString("\$beats[\$index]['status'] = 'pending';", $controller);
        self::assertStringContainsString("\$beats[\$beatIndex]['status'] = 'active';", $controller);
    }

    public function test_story_changes_participate_in_live_forge_revision(): void
    {
        $ajax = (string) file_get_contents($this->root('app/Tabletop/Http/TabletopAjaxController.php'));
        $view = (string) file_get_contents($this->root('app/Tabletop/Views/chamber.php'));

        self::assertStringContainsString("'story' => \$forge['story'] ?? []", $ajax);
        self::assertStringContainsString("'story' => \$dungeonForge['story'] ?? []", $view);
    }

    public function test_player_story_secrecy_boundary_from_iv_36_6_is_preserved(): void
    {
        $ajax = (string) file_get_contents($this->root('app/Tabletop/Http/TabletopAjaxController.php'));
        $view = (string) file_get_contents($this->root('app/Tabletop/Views/chamber.php'));

        self::assertStringContainsString("unset(\$forge['story'])", $ajax);
        self::assertStringContainsString("unset(\$dungeonForge['story'])", $view);
    }
}
