<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Campaigns;

use PHPUnit\Framework\TestCase;

final class FirstMapOnTableRegressionTest extends TestCase
{
    private function source(string $path): string
    {
        return (string) file_get_contents(dirname(__DIR__, 4) . '/' . $path);
    }

    public function test_creator_supports_blank_atlas_and_forge_first_map_paths(): void
    {
        $source = $this->source('app/Tabletop/Campaigns/TabletopCreator.php');
        self::assertStringContainsString("FIRST_MAP_BLANK = 'blank'", $source);
        self::assertStringContainsString("FIRST_MAP_ATLAS = 'atlas'", $source);
        self::assertStringContainsString("FIRST_MAP_FORGE = 'forge'", $source);
        self::assertStringContainsString("'The First Blank Page'", $source);
        self::assertStringContainsString("'Pippin\\'s Forge Workbench'", $source);
        self::assertStringContainsString('atlasSceneForKeeper(', $source);
    }

    public function test_atlas_first_map_is_keeper_owned_and_clones_surface_grid_without_moving_source(): void
    {
        $creator = $this->source('app/Tabletop/Campaigns/TabletopCreator.php');
        $manager = $this->source('app/Tables/Scenes/Services/TableSceneManager.php');
        self::assertStringContainsString('dungeonMasterUserId() !== $dungeonMasterUserId', $creator);
        self::assertStringContainsString('cloneForTable(', $creator);
        self::assertStringContainsString('$source->mapAttachmentId()', $manager);
        self::assertStringContainsString('$source->gridOffsetX()', $manager);
        self::assertStringContainsString('$source->gridReferenceWidth()', $manager);
        self::assertStringContainsString('$this->scenes->save($scene)', $manager);
    }

    public function test_campaign_atlas_creation_form_offers_three_first_map_choices_and_owned_uploaded_maps(): void
    {
        $shortcode = $this->source('app/Tabletop/Presentation/TabletopShortcode.php');
        $view = $this->source('app/Tabletop/Views/chamber.php');
        self::assertStringContainsString("'atlas_scenes' => \$atlasScenes", $shortcode);
        self::assertStringContainsString('$atlasScene->isGeneratedSurface()', $shortcode);
        self::assertStringContainsString('Begin with a Blank Table', $view);
        self::assertStringContainsString('Choose from the Atlas', $view);
        self::assertStringContainsString('Ask Pippin to Forge a Scene', $view);
        self::assertStringContainsString('data-first-map-atlas', $view);
    }

    public function test_creation_endpoint_and_client_carry_first_map_choice_and_forge_opens_workbench(): void
    {
        $controller = $this->source('app/Tabletop/Http/CreateTabletopAjaxController.php');
        $script = $this->source('assets/js/tabletop.js');
        self::assertStringContainsString("\$_POST['first_map']", $controller);
        self::assertStringContainsString("\$_POST['source_table_id']", $controller);
        self::assertStringContainsString("\$_POST['source_scene_id']", $controller);
        self::assertStringContainsString("first_map: firstMap", $script);
        self::assertStringContainsString("url.searchParams.set('first_map', 'forge')", $script);
        self::assertStringContainsString("document.querySelector('[data-dungeon-forge]')", $script);
        self::assertStringContainsString('forge.open = true', $script);
    }
}
