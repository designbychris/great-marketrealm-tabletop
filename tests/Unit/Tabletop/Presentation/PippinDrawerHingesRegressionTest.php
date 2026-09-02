<?php

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Presentation;

use PHPUnit\Framework\TestCase;

final class PippinDrawerHingesRegressionTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        $this->root = dirname(__DIR__, 4);
    }

    public function test_keeper_drawer_tabs_are_document_delegated_across_chamber_replacement(): void
    {
        $js = file_get_contents($this->root . '/assets/js/tabletop.js');

        self::assertStringContainsString('function setKeeperDrawerOpen(kind, open)', $js);
        self::assertStringContainsString("event.target.closest('[data-atlas-toggle]')", $js);
        self::assertStringContainsString("event.target.closest('[data-bestiary-toggle]')", $js);
        self::assertStringContainsString("setKeeperDrawerOpen('atlas', drawer?.dataset.open !== 'true');", $js);
        self::assertStringContainsString("setKeeperDrawerOpen('bestiary', drawer?.dataset.open !== 'true');", $js);
        self::assertStringContainsString("root.dataset.keeperDrawerOpen = open ? 'atlas' : '';", $js);
        self::assertStringContainsString("root.dataset.keeperDrawerOpen = open ? 'bestiary' : '';", $js);
    }

    public function test_generated_scene_names_are_unslashed_before_sanitising(): void
    {
        $controller = file_get_contents(
            $this->root . '/app/Tabletop/Http/DungeonForgeAjaxController.php'
        );

        self::assertStringContainsString(
            "sanitize_text_field(wp_unslash((string) (\$_POST['scene_name'] ?? '')))",
            $controller
        );
    }

    public function test_uploaded_atlas_scene_names_follow_the_same_apostrophe_contract(): void
    {
        $controller = file_get_contents(
            $this->root . '/app/Tabletop/Http/KeepersAtlasAjaxController.php'
        );

        self::assertStringContainsString(
            "sanitize_text_field(wp_unslash((string) (\$_POST['scene_name'] ?? '')))",
            $controller
        );
    }
}
