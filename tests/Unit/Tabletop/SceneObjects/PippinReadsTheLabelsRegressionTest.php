<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\SceneObjects;

use PHPUnit\Framework\TestCase;

final class PippinReadsTheLabelsRegressionTest extends TestCase
{
    private function source(string $path): string
    {
        return (string) file_get_contents(dirname(__DIR__, 4) . '/' . $path);
    }

    public function test_admin_persists_a_bounded_forge_label_vocabulary(): void
    {
        $admin = $this->source('app/Tabletop/SceneObjects/Admin/FurnitureCatalogueAdmin.php');

        self::assertStringContainsString('$allowedForgeTags = [', $admin);
        foreach (['mess','store','study','treasure','quarters','camp','cache','dungeon','village','forest','outdoor','market'] as $tag) {
            self::assertStringContainsString("'" . $tag . "'", $admin);
        }
        self::assertStringContainsString("'forge_tags' => \$forgeTags", $admin);
    }

    public function test_admin_exposes_room_and_environment_labels_without_free_text_taxonomy(): void
    {
        $admin = $this->source('app/Tabletop/SceneObjects/Admin/FurnitureCatalogueAdmin.php');

        self::assertStringContainsString('Dungeon Forge labels', $admin);
        self::assertStringContainsString("'Room purpose' => [", $admin);
        self::assertStringContainsString("'Environment' => [", $admin);
        self::assertStringContainsString('name="forge_tags[]"', $admin);
        self::assertStringNotContainsString('name="forge_tags" type="text"', $admin);
    }

    public function test_forge_only_considers_custom_opted_in_labelled_definitions(): void
    {
        $planner = $this->source('app/Tabletop/Cartography/Services/ForgeFurniturePlanner.php');

        self::assertStringContainsString("empty(\$definition['custom'])", $planner);
        self::assertStringContainsString("empty(\$definition['forge_enabled'])", $planner);
        self::assertStringContainsString("is_array(\$definition['forge_tags'] ?? null)", $planner);
        self::assertStringContainsString('array_intersect($tags, $context)', $planner);
    }

    public function test_context_includes_room_scene_and_market_outdoor_aliases(): void
    {
        $planner = $this->source('app/Tabletop/Cartography/Services/ForgeFurniturePlanner.php');

        self::assertStringContainsString('$context = [$role, $sceneType];', $planner);
        self::assertStringContainsString("\$context[] = 'outdoor';", $planner);
        self::assertStringContainsString("\$context[] = 'market';", $planner);
    }

    public function test_custom_selection_is_deterministic_sparse_and_bounded(): void
    {
        $planner = $this->source('app/Tabletop/Cartography/Services/ForgeFurniturePlanner.php');

        self::assertStringContainsString("'custom-use-' . \$roomIndex", $planner);
        self::assertStringContainsString("'custom-slot-' . \$roomIndex", $planner);
        self::assertStringContainsString("'custom-rotation-' . \$roomIndex", $planner);
        self::assertStringContainsString('if (count($candidates) >= 2)', $planner);
        self::assertStringContainsString('private function fraction(string $seed, string $salt): float', $planner);
    }

    public function test_custom_candidates_still_flow_through_existing_clearance_rules(): void
    {
        $planner = $this->source('app/Tabletop/Cartography/Services/ForgeFurniturePlanner.php');

        self::assertStringContainsString('$this->customCandidatesForContext(', $planner);
        self::assertStringContainsString('$this->insideRoom($footprint', $planner);
        self::assertStringContainsString('$this->nearDoor($gridX, $gridY', $planner);
        self::assertStringContainsString('$this->overlaps($footprint, $placed)', $planner);
    }

    public function test_forge_generated_custom_objects_keep_sprite_and_label_metadata(): void
    {
        $controller = $this->source('app/Tabletop/Http/DungeonForgeAjaxController.php');

        self::assertStringContainsString("'sprite_svg' => (string) (\$definition['sprite_svg'] ?? '')", $controller);
        self::assertStringContainsString("'forge_tags' => is_array(\$definition['forge_tags'] ?? null)", $controller);
        self::assertStringContainsString("'forge_room_role'", $controller);
        self::assertStringContainsString("'forge_generated' => true", $controller);
    }

    public function test_phase_does_not_create_a_second_furniture_or_forge_repository(): void
    {
        $planner = $this->source('app/Tabletop/Cartography/Services/ForgeFurniturePlanner.php');

        self::assertStringNotContainsString('CustomForgeFurnitureRepository', $planner);
        self::assertStringNotContainsString('RoomFurnitureRepository', $planner);
        self::assertStringContainsString('$this->catalogue->all()', $planner);
    }
}
