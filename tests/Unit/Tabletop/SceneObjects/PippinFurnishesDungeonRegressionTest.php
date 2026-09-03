<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\SceneObjects;

use PHPUnit\Framework\TestCase;

final class PippinFurnishesDungeonRegressionTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        $this->root = dirname(__DIR__, 4);
    }

    public function test_scene_objects_are_a_dedicated_scene_layer_not_background_art(): void
    {
        $model = file_get_contents($this->root . '/app/Tabletop/SceneObjects/Models/SceneObject.php');
        self::assertIsString($model);
        self::assertStringContainsString('private string $sceneId', $model);
        self::assertStringContainsString('private float $x', $model);
        self::assertStringContainsString('private float $y', $model);
        self::assertStringNotContainsString('attachmentId', $model);
    }

    public function test_scene_object_foundation_reserves_decorative_structural_and_interactive_categories(): void
    {
        $category = file_get_contents($this->root . '/app/Tabletop/SceneObjects/Models/SceneObjectCategory.php');
        self::assertIsString($category);
        self::assertStringContainsString("DECORATIVE = 'decorative'", $category);
        self::assertStringContainsString("STRUCTURAL = 'structural'", $category);
        self::assertStringContainsString("INTERACTIVE = 'interactive'", $category);
    }

    public function test_scene_objects_have_transform_state_and_future_property_seams(): void
    {
        $model = file_get_contents($this->root . '/app/Tabletop/SceneObjects/Models/SceneObject.php');
        self::assertIsString($model);
        self::assertStringContainsString('private int $rotation', $model);
        self::assertStringContainsString('private float $scale', $model);
        self::assertStringContainsString('private array $state', $model);
        self::assertStringContainsString('private array $properties', $model);
    }

    public function test_wordpress_repository_is_scene_scoped_and_uses_its_own_persistence_boundary(): void
    {
        $repository = file_get_contents($this->root . '/app/Tabletop/SceneObjects/Repositories/WordPressSceneObjectRepository.php');
        self::assertIsString($repository);
        self::assertStringContainsString("OPTION_NAME = 'gmrt_scene_objects'", $repository);
        self::assertStringContainsString('public function forScene(string $tableId, string $sceneId)', $repository);
        self::assertStringContainsString('public function clearScene(string $tableId, string $sceneId)', $repository);
    }
}
