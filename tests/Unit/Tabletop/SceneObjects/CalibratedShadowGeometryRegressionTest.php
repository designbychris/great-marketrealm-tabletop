<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\SceneObjects;

use PHPUnit\Framework\TestCase;

final class CalibratedShadowGeometryRegressionTest extends TestCase
{
    private function root(string $path): string
    {
        return dirname(__DIR__, 4) . '/' . ltrim($path, '/');
    }

    public function test_light_occluder_footprints_use_the_same_reference_width_scale_as_fog(): void
    {
        $light = file_get_contents($this->root(
            'app/Tabletop/SceneObjects/SceneObjectLightOcclusionProjector.php'
        ));

        self::assertIsString($light);
        self::assertStringContainsString('$referenceWidth = (float) $scene->gridReferenceWidth();', $light);
        self::assertStringContainsString('$gridScale = $referenceWidth > 0.0', $light);
        self::assertStringContainsString('$sceneWidth / $referenceWidth', $light);
        self::assertStringContainsString(
            '$grid = max(1.0, (float) $scene->gridSize() * $gridScale);',
            $light
        );
    }

    public function test_scene_object_vision_uses_the_same_calibrated_grid_for_blockers_and_cells(): void
    {
        $vision = file_get_contents($this->root(
            'app/Tabletop/SceneObjects/SceneObjectVisionProjector.php'
        ));

        self::assertIsString($vision);
        self::assertGreaterThanOrEqual(
            2,
            substr_count($vision, '$referenceWidth = (float) $scene->gridReferenceWidth();')
        );
        self::assertGreaterThanOrEqual(
            2,
            substr_count($vision, '$gridScale = $referenceWidth > 0.0')
        );
        self::assertStringContainsString(
            '$offsetX = (float) $scene->gridOffsetX() * $gridScale;',
            $vision
        );
        self::assertStringContainsString(
            '$offsetY = (float) $scene->gridOffsetY() * $gridScale;',
            $vision
        );
    }

    public function test_fog_mapper_remains_the_coordinate_contract(): void
    {
        $mapper = file_get_contents($this->root(
            'app/Tabletop/Fog/Services/FogCellMapper.php'
        ));
        $light = file_get_contents($this->root(
            'app/Tabletop/SceneObjects/SceneObjectLightOcclusionProjector.php'
        ));
        $vision = file_get_contents($this->root(
            'app/Tabletop/SceneObjects/SceneObjectVisionProjector.php'
        ));

        self::assertIsString($mapper);
        self::assertIsString($light);
        self::assertIsString($vision);
        self::assertStringContainsString('$scene->width() / $referenceWidth', $mapper);
        self::assertStringContainsString('$sceneWidth / $referenceWidth', $light);
        self::assertStringContainsString('$width / $referenceWidth', $vision);
    }

    public function test_visible_environmental_source_is_not_lost_to_projection_order(): void
    {
        $fog = file_get_contents($this->root(
            'app/Tabletop/Fog/Services/FogOfWarProjector.php'
        ));

        self::assertIsString($fog);
        self::assertStringContainsString(
            '|| in_array($sourceKey, $viewerLineOfSight, true)',
            $fog
        );
        self::assertStringContainsString(
            '|| in_array($sourceKey, $visible, true)',
            $fog
        );
        self::assertStringContainsString("'source_kind' => \$sourceKind", $fog);
    }
}
