<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class DungeonForgeCreatesWorldsRegressionTest extends TestCase
{
    private function source(string $path): string
    {
        return (string) file_get_contents(dirname(__DIR__, 4) . '/' . $path);
    }

    public function test_atlas_exposes_a_no_image_generate_dungeon_entry_point(): void
    {
        $view = $this->source('app/Tabletop/Views/chamber.php');

        self::assertStringContainsString('data-atlas-dungeon-forge', $view);
        self::assertStringContainsString('Generate Dungeon', $view);
        self::assertStringContainsString('No background image is required.', $view);
        self::assertStringContainsString('data-atlas-forge-create', $view);
    }

    public function test_generated_scenes_have_an_explicit_non_media_surface_kind(): void
    {
        $scene = $this->source('app/Tables/Scenes/Models/TableScene.php');
        $manager = $this->source('app/Tables/Scenes/Services/TableSceneManager.php');

        self::assertStringContainsString("['image', 'generated']", $scene);
        self::assertStringContainsString("\$surfaceKind === 'generated'", $scene);
        self::assertStringContainsString("'surface_kind' => \$this->surfaceKind", $scene);
        self::assertStringContainsString('public function createGenerated(', $manager);
        self::assertStringContainsString("'generated'", $manager);
    }

    public function test_atlas_forge_creates_and_privately_opens_a_new_scene(): void
    {
        $js = $this->source('assets/js/tabletop.js');
        $provider = $this->source('app/Tabletop/TabletopServiceProvider.php');

        self::assertStringContainsString("request('gmrt_forge_dungeon_world'", $js);
        self::assertStringContainsString('replaceChamber(`${message} Behind the Curtain for inspection.`, sceneId)', $js);
        self::assertStringContainsString("'wp_ajax_gmrt_forge_dungeon_world'", $provider);
        self::assertStringContainsString("[\$this->dungeonForgeAjax, 'createWorld']", $provider);
    }

    public function test_world_creation_builds_authoritative_walls_lights_and_fog_transactionally(): void
    {
        $controller = $this->source('app/Tabletop/Http/DungeonForgeAjaxController.php');

        self::assertStringContainsString('$this->sceneManager->createGenerated(', $controller);
        self::assertStringContainsString('$this->vision->addBatch(', $controller);
        self::assertStringContainsString('$this->lights->save($light)', $controller);
        self::assertStringContainsString('$this->fog->configure($tableId, $userId, true, true, $sceneId)', $controller);
        self::assertStringContainsString('$this->cleaner->clear($tableId, $scene->id())', $controller);
    }

    public function test_forge_themes_change_artwork_without_changing_geometry_authority(): void
    {
        $view = $this->source('app/Tabletop/Views/chamber.php');
        $js = $this->source('assets/js/tabletop.js');
        $css = $this->source('assets/css/tabletop.css');
        $controller = $this->source('app/Tabletop/Http/DungeonForgeAjaxController.php');

        self::assertStringContainsString('Pantry Stone', $view);
        self::assertStringContainsString('Frostreem Vault', $view);
        self::assertStringContainsString("theme = 'pantry-stone'", $js);
        self::assertStringContainsString("'theme' => \$plan['theme']", $controller);
        self::assertStringContainsString('themes only repaint the generated SVG surface', $css);
        self::assertStringContainsString('data-forge-theme="mushroom-grotto"', $css);
    }

    public function test_phase_is_documented_and_versioned(): void
    {
        $plugin = $this->source('great-marketrealm-tabletop.php');
        $roadmap = $this->source('ROADMAP.md');
        $phase = $this->source('docs/Roadmap/PHASE-IV.30.2A.md');

        self::assertStringContainsString('0.32.0-alpha.5', $plugin);
        self::assertStringContainsString('IV.30.2A — The Forge Creates Worlds', $roadmap);
        self::assertStringContainsString('Atlas → Generate Dungeon', $phase);
        self::assertStringContainsString('Behind the Curtain', $phase);
    }
}
