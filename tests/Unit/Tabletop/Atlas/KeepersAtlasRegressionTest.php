<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Atlas;

use PHPUnit\Framework\TestCase;

final class KeepersAtlasRegressionTest extends TestCase
{
    private function root(string $path): string
    {
        return dirname(__DIR__, 4) . '/' . $path;
    }

    public function test_atlas_register_is_dungeon_master_only(): void
    {
        $chamber = file_get_contents($this->root('app/Tabletop/Services/TabletopChamber.php'));
        $view = file_get_contents($this->root('app/Tabletop/Views/chamber.php'));

        self::assertStringContainsString('$viewer->isDungeonMaster()', $chamber);
        self::assertStringContainsString('? array_map(', $chamber);
        self::assertStringContainsString(': []', $chamber);
        self::assertStringContainsString("The Keeper's Atlas", $view);
        self::assertStringContainsString('$state->isDungeonMaster()', $view);
    }

    public function test_new_maps_are_inscribed_without_becoming_active(): void
    {
        $atlas = file_get_contents($this->root('app/Tabletop/Atlas/Services/KeepersAtlas.php'));

        self::assertStringContainsString('$this->scenes->create(', $atlas);
        self::assertStringNotContainsString('$this->scenes->activate($tableId, $scene->id())', $atlas);
        self::assertStringContainsString('addMap(', $atlas);
    }

    public function test_only_the_dungeon_master_may_add_or_open_maps(): void
    {
        $atlas = file_get_contents($this->root('app/Tabletop/Atlas/Services/KeepersAtlas.php'));

        self::assertStringContainsString('TableMemberRole::DUNGEON_MASTER', $atlas);
        self::assertStringContainsString('TableMemberStatus::ACTIVE', $atlas);
        self::assertStringContainsString("Only the Dungeon Master may open the Keeper's Atlas.", $atlas);
    }

    public function test_atlas_actions_are_nonce_guarded_and_server_authoritative(): void
    {
        $controller = file_get_contents($this->root('app/Tabletop/Http/KeepersAtlasAjaxController.php'));
        $provider = file_get_contents($this->root('app/Tabletop/TabletopServiceProvider.php'));

        self::assertStringContainsString('check_ajax_referer(TabletopAjaxController::NONCE_ACTION', $controller);
        self::assertStringContainsString("'wp_ajax_gmrt_atlas_add_map'", $provider);
        self::assertStringContainsString("'wp_ajax_gmrt_atlas_open_map'", $provider);
    }

    public function test_browser_uses_wordpress_media_library_and_explicit_open_scene_action(): void
    {
        $js = file_get_contents($this->root('assets/js/tabletop.js'));

        self::assertStringContainsString("gmrt_atlas_add_map", $js);
        self::assertStringContainsString("gmrt_atlas_open_map", $js);
        self::assertStringContainsString("Add a Map to the Keeper\\'s Atlas", $js);
        self::assertStringContainsString("window.wp.media", $js);
    }
}
