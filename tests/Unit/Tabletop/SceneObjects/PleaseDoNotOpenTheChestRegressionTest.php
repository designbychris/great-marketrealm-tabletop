<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\SceneObjects;

use PHPUnit\Framework\TestCase;

final class PleaseDoNotOpenTheChestRegressionTest extends TestCase
{
    private function root(string $path): string
    {
        return dirname(__DIR__, 4) . '/' . ltrim($path, '/');
    }

    public function test_chest_declares_an_explicit_open_close_interaction(): void
    {
        $catalogue = file_get_contents($this->root('app/Tabletop/SceneObjects/FurnitureCatalogue.php'));

        self::assertIsString($catalogue);
        self::assertStringContainsString("'Chest'", $catalogue);
        self::assertStringContainsString("'open_close'", $catalogue);
        self::assertStringContainsString("'interaction' => in_array(", $catalogue);
        self::assertStringContainsString("'mimic_capable' => true", $catalogue);
    }

    public function test_new_scene_objects_start_closed_and_persist_interaction_capability(): void
    {
        $view = file_get_contents($this->root('app/Tabletop/Views/chamber.php'));

        self::assertIsString($view);
        self::assertStringContainsString("'open' => false", $view);
        self::assertStringContainsString("'interaction' => (string) (\$definition['interaction'] ?? 'none')", $view);
        self::assertStringContainsString('data-scene-object-interaction=', $view);
        self::assertStringContainsString('data-scene-object-open=', $view);
    }

    public function test_keeper_interaction_re_resolves_exact_object_and_toggles_only_state(): void
    {
        $view = file_get_contents($this->root('app/Tabletop/Views/chamber.php'));

        self::assertIsString($view);
        self::assertStringContainsString("\$sceneObjectAction === 'interact'", $view);
        self::assertStringContainsString("\$interaction === 'open_close'", $view);
        self::assertStringContainsString("\$state['open'] = empty(\$state['open']);", $view);
        self::assertStringContainsString('$existingObject->properties()', $view);
        self::assertStringContainsString('$existingObject->rotation()', $view);
        self::assertStringContainsString('$existingObject->scale()', $view);
    }

    public function test_keeper_editor_only_enables_interact_for_interactive_objects(): void
    {
        $js = file_get_contents($this->root('assets/js/tabletop.js'));
        $view = file_get_contents($this->root('app/Tabletop/Views/chamber.php'));

        self::assertIsString($js);
        self::assertIsString($view);
        self::assertStringContainsString('data-scene-object-interact', $view);
        self::assertStringContainsString("object?.dataset.sceneObjectInteraction || 'none'", $js);
        self::assertStringContainsString("furnitureInteract.textContent = interaction === 'open_close'", $js);
        self::assertStringContainsString("submitSceneObjectAction('interact'", $js);
    }

    public function test_open_state_has_a_pixel_visual_without_changing_tactical_traits(): void
    {
        $css = file_get_contents($this->root('assets/css/tabletop.css'));
        $view = file_get_contents($this->root('app/Tabletop/Views/chamber.php'));

        self::assertIsString($css);
        self::assertIsString($view);
        self::assertStringContainsString('.gmrt-scene-object--chest.is-open', $css);
        self::assertStringContainsString('--gmrt-chest-lid-lift: -5px', $css);
        self::assertStringContainsString('data-scene-object-open=', $view);
        self::assertStringContainsString('$objectOpen ? \'true\' : \'false\'', $view);
        self::assertStringContainsString('data-blocks-movement=', $view);
        self::assertStringContainsString('data-light-occlusion=', $view);
    }

    public function test_phase_does_not_implement_mimic_conversion_or_inventory_loot_yet(): void
    {
        $js = file_get_contents($this->root('assets/js/tabletop.js'));
        $view = file_get_contents($this->root('app/Tabletop/Views/chamber.php'));

        self::assertIsString($js);
        self::assertIsString($view);
        self::assertStringNotContainsString('convert_to_mimic', $js);
        self::assertStringNotContainsString('gmrt_scene_object_loot', $view);
        self::assertStringNotContainsString('inventory_transfer', $view);
    }
}
