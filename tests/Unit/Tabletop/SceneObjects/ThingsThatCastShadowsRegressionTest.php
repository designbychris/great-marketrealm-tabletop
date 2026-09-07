<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\SceneObjects;

use PHPUnit\Framework\TestCase;

final class ThingsThatCastShadowsRegressionTest extends TestCase
{
    private function root(string $path): string
    {
        return dirname(__DIR__, 4) . '/' . ltrim($path, '/');
    }

    public function test_catalogue_declares_independent_light_occlusion_strengths(): void
    {
        $catalogue = file_get_contents($this->root('app/Tabletop/SceneObjects/FurnitureCatalogue.php'));

        self::assertIsString($catalogue);
        self::assertStringContainsString('float $lightOcclusion', $catalogue);
        self::assertStringContainsString("'light_occlusion' => max(0.0, min(1.0, \$lightOcclusion))", $catalogue);
        self::assertStringContainsString("false,\n                0.15,", $catalogue);
        self::assertStringContainsString("false,\n                0.45,", $catalogue);
        self::assertStringContainsString("false,\n                0.55,", $catalogue);
        self::assertStringContainsString("false,\n                0.70,", $catalogue);
        self::assertStringContainsString("true,\n                1.00,", $catalogue);
        self::assertStringNotContainsString("blocksVision ? 1", $catalogue);
    }

    public function test_new_and_older_scene_objects_share_the_same_occlusion_defaults(): void
    {
        $view = file_get_contents($this->root('app/Tabletop/Views/chamber.php'));

        self::assertIsString($view);
        self::assertStringContainsString("'light_occlusion' => max(0.0, min(1.0, (float) (\$definition['light_occlusion'] ?? 0.0)))", $view);
        self::assertStringContainsString("\$objectProperties['light_occlusion']", $view);
        self::assertStringContainsString("\$objectDefinition['light_occlusion'] ?? 0.0", $view);
        self::assertStringContainsString('data-light-occlusion=', $view);
    }

    public function test_occlusion_projector_uses_rotated_scaled_object_footprints_and_compounds_transmission(): void
    {
        $projector = file_get_contents($this->root('app/Tabletop/SceneObjects/SceneObjectLightOcclusionProjector.php'));

        self::assertIsString($projector);
        self::assertStringContainsString('final class SceneObjectLightOcclusionProjector', $projector);
        self::assertStringContainsString('public function occlusionBetween(', $projector);
        self::assertStringContainsString('public function occluders(TableScene $scene, array $objects): array', $projector);
        self::assertStringContainsString("\$properties['light_occlusion']", $projector);
        self::assertStringContainsString('$object->scale()', $projector);
        self::assertStringContainsString('$object->rotation()', $projector);
        self::assertStringContainsString('lineIntersectsPolygon(', $projector);
        self::assertStringContainsString('$transmission *= 1.0 - $occluder[\'occlusion\'];', $projector);
        self::assertStringContainsString('return $this->clamp01(1.0 - $transmission);', $projector);
    }

    public function test_shape_of_darkness_does_not_replace_the_existing_lighting_engine(): void
    {
        $projector = file_get_contents($this->root('app/Tabletop/SceneObjects/SceneObjectLightOcclusionProjector.php'));
        $fog = file_get_contents($this->root('app/Tabletop/Fog/Services/FogOfWarProjector.php'));

        self::assertIsString($projector);
        self::assertIsString($fog);
        self::assertStringNotContainsString('FogOfWarProjector', $projector);
        self::assertStringNotContainsString('EnvironmentalLight', $projector);
        self::assertStringNotContainsString('DroppedLight', $projector);
        self::assertStringContainsString('instanceof EnvironmentalLight', $fog);
        self::assertStringContainsString('instanceof DroppedLight', $fog);
        self::assertStringContainsString('SceneObjectVisionProjector', $fog);
    }
}
