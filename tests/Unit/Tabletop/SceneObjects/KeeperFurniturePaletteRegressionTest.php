<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\SceneObjects;

use PHPUnit\Framework\TestCase;

final class KeeperFurniturePaletteRegressionTest extends TestCase
{
    private function root(string $path): string
    {
        return dirname(__DIR__, 4) . '/' . ltrim($path, '/');
    }

    public function test_palette_registers_the_first_six_furnishings_and_every_one_can_become_a_mimic(): void
    {
        $catalogue = file_get_contents($this->root('app/Tabletop/SceneObjects/FurnitureCatalogue.php'));

        foreach (['table', 'chair', 'chest', 'barrel', 'crate', 'bookshelf'] as $kind) {
            self::assertStringContainsString("'{$kind}' =>", $catalogue);
        }
        self::assertSame(6, substr_count($catalogue, '$this->definition('));
        self::assertStringContainsString("'mimic_capable' => true", $catalogue);
    }

    public function test_keeper_places_scene_scoped_objects_through_the_chamber(): void
    {
        $view = file_get_contents($this->root('app/Tabletop/Views/chamber.php'));

        self::assertStringContainsString('$state->isDungeonMaster()', $view);
        self::assertStringContainsString("wp_verify_nonce(\$submittedNonce, 'gmrt_scene_object_place')", $view);
        self::assertStringContainsString('hash_equals($projectedSceneId, $submittedSceneId)', $view);
        self::assertStringContainsString('new SceneObject(', $view);
        self::assertStringContainsString('data-furniture-palette', $view);
        self::assertStringContainsString('data-scene-object-layer', $view);
        self::assertStringContainsString('data-mimic-capable', $view);
    }

    public function test_browser_normalises_clicks_and_reuses_the_living_table_heartbeat(): void
    {
        $js = file_get_contents($this->root('assets/js/tabletop.js'));

        self::assertStringContainsString("body.set('gmrt_scene_object_action', 'place')", $js);
        self::assertStringContainsString("body.set('gmrt_scene_object_scene_id', projectedSceneId)", $js);
        self::assertStringContainsString('(event.clientX - rect.left) / Math.max(1, rect.width)', $js);
        self::assertStringContainsString('(event.clientY - rect.top) / Math.max(1, rect.height)', $js);
        self::assertStringContainsString("request('gmrt_tabletop_fragment', {})", $js);
        self::assertStringContainsString('await refreshSceneObjectLayer();', $js);
        self::assertStringNotContainsString('setInterval(refreshSceneObjectLayer', $js);
    }

    public function test_world_objects_render_below_tokens_and_editing_waits_for_the_next_phase(): void
    {
        $view = file_get_contents($this->root('app/Tabletop/Views/chamber.php'));
        $css = file_get_contents($this->root('assets/css/tabletop.css'));
        $objectPos = strpos($view, 'data-scene-object-layer');
        $tokenPos = strpos($view, 'class="gmrt-board__tokens"');

        self::assertNotFalse($objectPos);
        self::assertNotFalse($tokenPos);
        self::assertLessThan($tokenPos, $objectPos);
        self::assertStringContainsString('--gmrt-battlefield-z-objects: 5', $css);
        self::assertStringContainsString('--gmrt-battlefield-z-tokens: 10', $css);
        self::assertStringNotContainsString('data-scene-object-remove', $view);
    }
}
