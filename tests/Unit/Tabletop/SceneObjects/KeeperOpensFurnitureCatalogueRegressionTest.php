<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\SceneObjects;

use PHPUnit\Framework\TestCase;

final class KeeperOpensFurnitureCatalogueRegressionTest extends TestCase
{
    private function source(string $path): string
    {
        return (string) file_get_contents(dirname(__DIR__, 4) . '/' . $path);
    }

    public function test_custom_furniture_has_a_wordpress_backed_repository(): void
    {
        $repo = $this->source('app/Tabletop/SceneObjects/Repositories/WordPressCustomFurnitureRepository.php');

        self::assertStringContainsString("gmrt_custom_furniture_catalogue", $repo);
        self::assertStringContainsString('public function all(): array', $repo);
        self::assertStringContainsString('public function save(string $kind, array $definition): void', $repo);
        self::assertStringContainsString('public function remove(string $kind): void', $repo);
    }

    public function test_catalogue_merges_custom_records_without_allowing_builtin_shadowing(): void
    {
        $catalogue = $this->source('app/Tabletop/SceneObjects/FurnitureCatalogue.php');

        self::assertStringContainsString('public function builtIns(): array', $catalogue);
        self::assertStringContainsString('$builtIns + $custom', $catalogue);
        self::assertStringContainsString('public function isBuiltIn(string $kind): bool', $catalogue);
        self::assertStringContainsString('WordPressCustomFurnitureRepository', $catalogue);
    }

    public function test_svg_boundary_rejects_active_or_external_content(): void
    {
        $svg = $this->source('app/Tabletop/SceneObjects/FurnitureSvgSanitizer.php');

        self::assertStringContainsString('LIBXML_NONET', $svg);
        self::assertStringContainsString('MAX_BYTES = 262144', $svg);
        self::assertStringNotContainsString("'foreignObject'", $svg);
        self::assertStringNotContainsString("'script'", $svg);
        self::assertStringContainsString('javascript:', $svg);
        self::assertStringContainsString("str_starts_with(strtolower(\$name), 'on')", $svg);
    }

    public function test_admin_page_is_manage_options_nonce_protected_and_supports_crud(): void
    {
        $admin = $this->source('app/Tabletop/SceneObjects/Admin/FurnitureCatalogueAdmin.php');

        self::assertStringContainsString("add_management_page(", $admin);
        self::assertStringContainsString("'manage_options'", $admin);
        self::assertStringContainsString("check_admin_referer(self::NONCE)", $admin);
        self::assertStringContainsString('gmrt_save_custom_furniture', $admin);
        self::assertStringContainsString('gmrt_delete_custom_furniture', $admin);
        self::assertStringContainsString('$this->custom->save($kind, $definition)', $admin);
        self::assertStringContainsString('$this->custom->remove($kind)', $admin);
    }

    public function test_admin_definition_exposes_the_existing_scene_object_traits(): void
    {
        $admin = $this->source('app/Tabletop/SceneObjects/Admin/FurnitureCatalogueAdmin.php');

        foreach (['width_units','height_units','blocks_movement','cover','blocks_vision','light_occlusion','interaction','mimic_capable','forge_enabled'] as $field) {
            self::assertStringContainsString($field, $admin);
        }
        self::assertStringContainsString("accept=\".svg,image/svg+xml\"", $admin);
        self::assertStringContainsString('Suspicious Ottoman', $admin);
    }

    public function test_custom_svg_is_rendered_in_palette_and_scene_object_layer(): void
    {
        $view = $this->source('app/Tabletop/Views/chamber.php');
        $css = $this->source('assets/css/tabletop.css');

        self::assertStringContainsString("['sprite_svg']", $view);
        self::assertStringContainsString('gmrt-furniture-choice__sprite--custom', $view);
        self::assertStringContainsString('gmrt-scene-object__custom-sprite', $view);
        self::assertStringContainsString('gmrt-scene-object__custom-sprite svg', $css);
    }

    public function test_sprite_svg_is_persisted_with_placed_custom_scene_objects(): void
    {
        $view = $this->source('app/Tabletop/Views/chamber.php');

        self::assertStringContainsString("'sprite_svg' => (string) (\$definition['sprite_svg'] ?? '')", $view);
        self::assertStringContainsString("'mimic_capable' => ! empty(\$definition['mimic_capable'])", $view);
    }

    public function test_service_provider_registers_the_admin_catalogue_boundary(): void
    {
        $provider = $this->source('app/Tabletop/TabletopServiceProvider.php');

        self::assertStringContainsString('private FurnitureCatalogueAdmin $furnitureCatalogueAdmin;', $provider);
        self::assertStringContainsString('$this->furnitureCatalogueAdmin->register();', $provider);
        self::assertStringContainsString('new WordPressCustomFurnitureRepository()', $provider);
        self::assertStringContainsString('new FurnitureSvgSanitizer()', $provider);
        self::assertStringContainsString('new FurnitureCatalogue($customFurniture)', $provider);
    }
}
