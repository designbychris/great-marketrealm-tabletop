<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class AtlasFindsTheDoorRegressionTest extends TestCase
{
    private function source(string $path): string
    {
        return (string) file_get_contents(dirname(__DIR__, 4) . '/' . $path);
    }

    public function test_keeper_atlas_generate_scene_exposes_way_in_control(): void
    {
        $view = $this->source('app/Tabletop/Views/chamber.php');

        self::assertStringContainsString('data-atlas-forge-entry', $view);
        self::assertStringContainsString('<option value="none" selected>None</option>', $view);
        self::assertStringContainsString('<option value="entrance">Main entrance</option>', $view);
        self::assertStringContainsString('<option value="portal">Arrival portal</option>', $view);
    }

    public function test_atlas_forge_reads_the_way_in_selection(): void
    {
        $js = $this->source('assets/js/tabletop.js');

        self::assertStringContainsString(
            "const atlasForgeEntry = document.querySelector('[data-atlas-forge-entry]');",
            $js
        );
        self::assertStringContainsString(
            "const entryMode = forgeEntryMode(atlasForgeEntry?.value || 'none');",
            $js
        );
    }

    public function test_atlas_forge_passes_entry_mode_to_the_shared_scene_generator(): void
    {
        $js = $this->source('assets/js/tabletop.js');

        self::assertStringContainsString(
            'generateSceneForgePlan(sceneType, seed, style, theme, aspectByStyle[style] || .7, entryMode)',
            $js
        );
    }

    public function test_atlas_forge_reuses_the_certified_shared_entry_pipeline(): void
    {
        $js = $this->source('assets/js/tabletop.js');

        self::assertStringContainsString(
            "const generateSceneForgePlan = (sceneType, seed, style, theme = 'pantry-stone', preferredAspect = null, entryMode = 'none') =>",
            $js
        );
        self::assertStringContainsString("return generateDungeonForgePlan(seed, style, theme, preferredAspect, mode);", $js);
    }
}
