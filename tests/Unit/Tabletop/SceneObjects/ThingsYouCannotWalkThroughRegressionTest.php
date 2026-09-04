<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\SceneObjects;

use PHPUnit\Framework\TestCase;

final class ThingsYouCannotWalkThroughRegressionTest extends TestCase
{
    private function root(string $path): string
    {
        return dirname(__DIR__, 4) . '/' . ltrim($path, '/');
    }

    public function test_catalogue_declares_explicit_movement_blockers_without_using_category_as_collision(): void
    {
        $catalogue = file_get_contents($this->root('app/Tabletop/SceneObjects/FurnitureCatalogue.php'));

        self::assertStringContainsString("'blocks_movement' => \$blocksMovement", $catalogue);
        self::assertStringContainsString("'Chair',\n                SceneObjectCategory::DECORATIVE,\n                0.75,\n                0.75,\n                false,", $catalogue);
        self::assertGreaterThanOrEqual(5, substr_count($catalogue, "                true,\n                '"));
    }

    public function test_scene_object_projection_exposes_collision_geometry_and_backfills_older_furniture(): void
    {
        $view = file_get_contents($this->root('app/Tabletop/Views/chamber.php'));

        self::assertStringContainsString("'blocks_movement' => ! empty(\$definition['blocks_movement'])", $view);
        self::assertStringContainsString("array_key_exists('blocks_movement', \$objectProperties)", $view);
        self::assertStringContainsString('data-blocks-movement="<?php echo $objectBlocksMovement', $view);
        self::assertStringContainsString('data-scene-object-width-units=', $view);
        self::assertStringContainsString('data-scene-object-height-units=', $view);
    }

    public function test_token_movement_uses_rotated_scene_object_footprints_and_sweeps_the_route(): void
    {
        $js = file_get_contents($this->root('assets/js/tabletop.js'));

        self::assertStringContainsString('function rectangleCorners(', $js);
        self::assertStringContainsString('function polygonsOverlap(', $js);
        self::assertStringContainsString('[data-scene-object-id][data-blocks-movement="true"]', $js);
        self::assertStringContainsString('Number(object.dataset.sceneObjectRotation || 0)', $js);
        self::assertStringContainsString('function movementPathBlocked(token, from, to)', $js);
        self::assertStringContainsString('Math.ceil(distancePixels / stride)', $js);
        self::assertStringContainsString('if (movementPathBlocked(selected, origin, destination))', $js);
        self::assertStringContainsString('if (movementPathBlocked(token, from, point))', $js);
        self::assertStringContainsString('tokenDrag.lastValidPoint = point;', $js);
        self::assertStringContainsString('Pippin refuses to draw the token inside the furniture.', $js);
    }

    public function test_collision_is_movement_only_and_does_not_preempt_later_cover_or_light_phases(): void
    {
        $js = file_get_contents($this->root('assets/js/tabletop.js'));
        $catalogue = file_get_contents($this->root('app/Tabletop/SceneObjects/FurnitureCatalogue.php'));

        self::assertStringNotContainsString('blocks_vision', $catalogue);
        self::assertStringNotContainsString('light_occlusion', $catalogue);
        self::assertStringNotContainsString('cover_value', $catalogue);
        self::assertStringContainsString("request('gmrt_move_token'", $js);
    }
}
