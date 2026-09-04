<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\SceneObjects;

use PHPUnit\Framework\TestCase;

final class BookshelfBlocksViewRegressionTest extends TestCase
{
    private function root(string $path): string
    {
        return dirname(__DIR__, 4) . '/' . ltrim($path, '/');
    }

    public function test_scene_object_vision_projector_uses_rotated_scaled_furniture_geometry(): void
    {
        $projector = file_get_contents($this->root('app/Tabletop/SceneObjects/SceneObjectVisionProjector.php'));

        self::assertIsString($projector);
        self::assertStringContainsString('final class SceneObjectVisionProjector', $projector);
        self::assertStringContainsString('filterVisibleCells(', $projector);
        self::assertStringContainsString("array_key_exists('blocks_vision', \$properties)", $projector);
        self::assertStringContainsString("\$definition['blocks_vision']", $projector);
        self::assertStringContainsString("\$properties['width_units']", $projector);
        self::assertStringContainsString("\$properties['height_units']", $projector);
        self::assertStringContainsString('$object->scale()', $projector);
        self::assertStringContainsString('$object->rotation()', $projector);
        self::assertStringContainsString('rectangleCorners(', $projector);
        self::assertStringContainsString('lineIntersectsPolygon(', $projector);
    }

    public function test_bookshelf_visibility_is_backfilled_without_migrating_old_objects(): void
    {
        $projector = file_get_contents($this->root('app/Tabletop/SceneObjects/SceneObjectVisionProjector.php'));
        $catalogue = file_get_contents($this->root('app/Tabletop/SceneObjects/FurnitureCatalogue.php'));

        self::assertIsString($projector);
        self::assertIsString($catalogue);
        self::assertStringContainsString('$catalogue->find($object->kind())', $projector);
        self::assertStringContainsString("'Bookshelf'", $catalogue);
        self::assertStringContainsString("true,\n                'full',\n                true", $catalogue);
        self::assertStringNotContainsString('update_option(', $projector);
    }

    public function test_living_veil_filters_character_sight_server_side_before_token_projection(): void
    {
        $fog = file_get_contents($this->root('app/Tabletop/Fog/Services/FogOfWarProjector.php'));

        self::assertIsString($fog);
        self::assertStringContainsString('new WordPressSceneObjectRepository()', $fog);
        self::assertStringContainsString('->forScene(', $fog);
        self::assertStringContainsString('$candidate->tableId()', $fog);
        self::assertStringContainsString('$scene->id()', $fog);
        self::assertStringContainsString('new SceneObjectVisionProjector()', $fog);
        self::assertSame(2, substr_count($fog, '->filterVisibleCells('));
        self::assertStringContainsString('$tokenVisible = $mapper->visibleAround(', $fog);
        self::assertStringContainsString('$viewerSight = $mapper->visibleAround($scene, $token, $barriers, 60);', $fog);
        self::assertStringContainsString("'has_blockers' => \$barriers !== [] || \$sceneObjects !== []", $fog);
    }

    public function test_view_blocking_does_not_preempt_light_attenuation_or_duplicate_wall_persistence(): void
    {
        $fog = file_get_contents($this->root('app/Tabletop/Fog/Services/FogOfWarProjector.php'));
        $projector = file_get_contents($this->root('app/Tabletop/SceneObjects/SceneObjectVisionProjector.php'));

        self::assertIsString($fog);
        self::assertIsString($projector);
        self::assertStringContainsString('$illuminated = $mapper->visibleAround($scene, $lightSource, $barriers, $lightRadius);', $fog);
        self::assertStringContainsString('array_intersect($illuminated, $viewerLineOfSight)', $fog);
        self::assertStringNotContainsString('light_occlusion', $projector);
        self::assertStringNotContainsString('light_attenuation', $projector);
        self::assertStringNotContainsString('VisionBarrierRepository', $projector);
        self::assertStringNotContainsString('save(', $projector);
    }
}
