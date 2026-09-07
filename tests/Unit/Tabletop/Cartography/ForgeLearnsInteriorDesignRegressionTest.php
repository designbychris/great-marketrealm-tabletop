<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class ForgeLearnsInteriorDesignRegressionTest extends TestCase
{
    private function source(string $path): string
    {
        return (string) file_get_contents(dirname(__DIR__, 4) . '/' . $path);
    }

    public function test_forge_has_a_dedicated_deterministic_furniture_planner(): void
    {
        $planner = $this->source('app/Tabletop/Cartography/Services/ForgeFurniturePlanner.php');

        self::assertStringContainsString('final class ForgeFurniturePlanner', $planner);
        self::assertStringContainsString("hash('sha256', \$seed . '|' . \$salt)", $planner);
        self::assertStringContainsString('private function roomRole(', $planner);
        self::assertStringContainsString('private function candidatesForRole(', $planner);
    }

    public function test_room_roles_choose_contextual_catalogue_furniture(): void
    {
        $planner = $this->source('app/Tabletop/Cartography/Services/ForgeFurniturePlanner.php');

        foreach (['mess', 'store', 'study', 'treasure', 'quarters', 'camp', 'cache'] as $role) {
            self::assertStringContainsString("'" . $role . "'", $planner);
        }
        foreach (['table', 'chair', 'chest', 'barrel', 'crate', 'bookshelf'] as $kind) {
            self::assertStringContainsString("'kind' => '" . $kind . "'", $planner);
        }
    }

    public function test_furnishing_pass_keeps_doors_and_overlaps_clear(): void
    {
        $planner = $this->source('app/Tabletop/Cartography/Services/ForgeFurniturePlanner.php');

        self::assertStringContainsString('private function nearDoor(', $planner);
        self::assertStringContainsString('private function overlaps(', $planner);
        self::assertStringContainsString('private function insideRoom(', $planner);
        self::assertStringContainsString('Keep a broad two-square approach clear', $planner);
    }

    public function test_forge_persists_real_scene_objects_not_svg_decoration(): void
    {
        $controller = $this->source('app/Tabletop/Http/DungeonForgeAjaxController.php');

        self::assertStringContainsString('$furnitureDrafts = $this->furnisher->plan($plan);', $controller);
        self::assertStringContainsString('$this->sceneObjects->save(new SceneObject(', $controller);
        self::assertStringContainsString("'forge_generated' => true", $controller);
        self::assertStringContainsString("'forge_room_role'", $controller);
        self::assertStringNotContainsString('furnitureSvg', $controller);
    }

    public function test_generated_furniture_inherits_the_existing_tactical_and_interaction_contract(): void
    {
        $controller = $this->source('app/Tabletop/Http/DungeonForgeAjaxController.php');

        self::assertStringContainsString("'blocks_movement' => ! empty(\$definition['blocks_movement'])", $controller);
        self::assertStringContainsString("'cover' => (string) (\$definition['cover'] ?? 'none')", $controller);
        self::assertStringContainsString("'blocks_vision' => ! empty(\$definition['blocks_vision'])", $controller);
        self::assertStringContainsString("'light_occlusion'", $controller);
        self::assertStringContainsString("'interaction' => (string) (\$definition['interaction'] ?? 'none')", $controller);
        self::assertStringContainsString("'mimic_capable' => ! empty(\$definition['mimic_capable'])", $controller);
        self::assertStringContainsString("['open' => false]", $controller);
    }

    public function test_projection_and_keeper_message_report_the_furnishing_pass(): void
    {
        $controller = $this->source('app/Tabletop/Http/DungeonForgeAjaxController.php');
        $js = $this->source('assets/js/tabletop.js');

        self::assertStringContainsString("'version' => 4", $controller);
        self::assertStringContainsString("'furniture' => \$furnitureDrafts", $controller);
        self::assertStringContainsString("'furniture_ids' => \$furnitureIds", $controller);
        self::assertStringContainsString('%d furnishings', $controller);
        self::assertStringContainsString('Walls, doors, furniture, lights, grid and Fog are now authoritative.', $js);
    }

    public function test_scene_cleanup_includes_flat_scene_object_records(): void
    {
        $cleaner = $this->source('app/Tabletop/Atlas/Services/SceneShelfCleaner.php');

        self::assertStringContainsString("forgetFlatSceneRows('gmrt_scene_objects'", $cleaner);
        self::assertStringContainsString("(\$row['table_id'] ?? '') === \$tableId", $cleaner);
        self::assertStringContainsString("(\$row['scene_id'] ?? '') === \$sceneId", $cleaner);
    }

    public function test_provider_wires_the_existing_scene_object_repository_and_catalogue(): void
    {
        $provider = $this->source('app/Tabletop/TabletopServiceProvider.php');

        self::assertStringContainsString('SceneObjects\\Repositories\\WordPressSceneObjectRepository()', $provider);
        self::assertStringContainsString('SceneObjects\\FurnitureCatalogue()', $provider);
        self::assertStringContainsString('Cartography\\Services\\ForgeFurniturePlanner(', $provider);
    }
}
