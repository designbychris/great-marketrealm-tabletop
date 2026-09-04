<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\SceneObjects;

use PHPUnit\Framework\TestCase;

final class PippinRearrangesFurnitureRegressionTest extends TestCase
{
    private function root(string $path): string
    {
        return dirname(__DIR__, 4) . '/' . ltrim($path, '/');
    }

    public function test_furniture_palette_now_lives_inside_dungeon_master_controls(): void
    {
        $view = file_get_contents($this->root('app/Tabletop/Views/chamber.php'));
        $controls = strpos($view, 'data-keeper-controls');
        $palette = strpos($view, 'data-furniture-palette');
        $controlsClose = strpos($view, '</details>', $palette === false ? 0 : $palette);

        self::assertNotFalse($controls);
        self::assertNotFalse($palette);
        self::assertGreaterThan($controls, $palette);
        self::assertNotFalse($controlsClose);
        self::assertStringContainsString("wp_create_nonce('gmrt_scene_object_author')", $view);
        self::assertStringContainsString('data-furniture-editor', $view);
    }

    public function test_keeper_can_move_rotate_scale_duplicate_and_delete_exact_scene_objects(): void
    {
        $view = file_get_contents($this->root('app/Tabletop/Views/chamber.php'));

        self::assertStringContainsString("wp_verify_nonce(\$submittedNonce, 'gmrt_scene_object_author')", $view);
        self::assertStringContainsString('hash_equals($projectedSceneId, $submittedSceneId)', $view);
        self::assertStringContainsString('$sceneObjectRepository->find($tableIdForObjects, $projectedSceneId, $objectId)', $view);
        self::assertStringContainsString("in_array(\$sceneObjectAction, ['move', 'rotate', 'scale'], true)", $view);
        self::assertStringContainsString("\$sceneObjectAction === 'duplicate'", $view);
        self::assertStringContainsString("\$sceneObjectAction === 'delete'", $view);
        self::assertStringContainsString('max(0.5, min(2.5, $scale))', $view);
        self::assertStringContainsString('data-scene-object-rotation', $view);
        self::assertStringContainsString('data-scene-object-scale', $view);
    }

    public function test_browser_selects_and_drags_only_the_object_while_empty_battlefield_remains_free(): void
    {
        $js = file_get_contents($this->root('assets/js/tabletop.js'));
        $css = file_get_contents($this->root('assets/css/tabletop.css'));

        self::assertStringContainsString("sceneObjectLayer?.addEventListener('pointerdown'", $js);
        self::assertStringContainsString("event.target.closest('[data-scene-object-id]')", $js);
        self::assertStringContainsString("submitSceneObjectAction('move'", $js);
        self::assertStringContainsString("submitSceneObjectAction('rotate'", $js);
        self::assertStringContainsString("submitSceneObjectAction('scale'", $js);
        self::assertStringContainsString("submitSceneObjectAction('duplicate'", $js);
        self::assertStringContainsString("submitSceneObjectAction('delete'", $js);
        self::assertStringContainsString('.gmrt-scene-object-layer { position:absolute; inset:0; z-index:var(--gmrt-battlefield-z-objects); pointer-events:none; }', $css);
        self::assertStringContainsString('.gmrt-scene-object-layer.is-keeper-editable .gmrt-scene-object { pointer-events:auto; cursor:grab; }', $css);
        self::assertStringNotContainsString('setInterval(refreshSceneObjectLayer', $js);
    }

    public function test_keeper_can_toggle_grid_snapping_for_furniture_placement_and_dragging(): void
    {
        $view = file_get_contents($this->root('app/Tabletop/Views/chamber.php'));
        $js = file_get_contents($this->root('assets/js/tabletop.js'));

        self::assertStringContainsString('data-furniture-snap checked', $view);
        self::assertStringContainsString('data-scene-width=', $view);
        self::assertStringContainsString('data-scene-height=', $view);
        self::assertStringContainsString('data-grid-size=', $view);
        self::assertStringContainsString('data-grid-offset-x=', $view);
        self::assertStringContainsString('data-grid-offset-y=', $view);
        self::assertStringContainsString("const furnitureSnap = document.querySelector('[data-furniture-snap]')", $js);
        self::assertStringContainsString('function furniturePoint(point)', $js);
        self::assertStringContainsString("if (!furnitureSnap?.checked || !furniturePalette) return point;", $js);
        self::assertSame(2, substr_count($js, 'furniturePoint(coordinatesFromPointer(event))'));
        self::assertStringContainsString('Snap to Grid disabled. Pippin is trying not to look.', $js);
        self::assertStringNotContainsString("body.set('gmrt_scene_object_snap'", $js);
    }
}
