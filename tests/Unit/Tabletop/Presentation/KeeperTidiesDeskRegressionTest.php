<?php

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Presentation;

use PHPUnit\Framework\TestCase;

final class KeeperTidiesDeskRegressionTest extends TestCase
{
    private function source(string $path): string
    {
        return (string) file_get_contents(dirname(__DIR__, 4) . '/' . $path);
    }

    public function test_bestiary_workspace_rises_to_the_atlas_top_edge_without_moving_its_rail_tab(): void
    {
        $css = $this->source('assets/css/tabletop.css');
        self::assertStringContainsString('.gmrt-bestiary-drawer__panel {', $css);
        self::assertStringContainsString('top: -8.75rem;', $css);
        self::assertStringContainsString('height: calc(100% + 8.75rem);', $css);
    }

    public function test_live_gathering_refresh_preserves_character_portrait_seats(): void
    {
        $js = $this->source('assets/js/tabletop.js');
        self::assertStringContainsString("roleBadge.className = 'gmrt-party__role gmrt-party__seat'", $js);
        self::assertStringContainsString("const characterImage = companionToken && companionToken.image_url", $js);
        self::assertStringContainsString("roleBadge.setAttribute('aria-label', 'Playing: ' + characterName)", $js);
        self::assertStringContainsString("roleBadge.textContent = 'P';", $js);
    }

    public function test_fellowship_ribbon_colours_survive_the_pixel_button_skin(): void
    {
        $css = $this->source('assets/css/tabletop.css');
        self::assertStringContainsString('.gmrt-chamber .gmrt-fellowship-colour__swatches button {', $css);
        self::assertStringContainsString('background: var(--gmrt-swatch) !important;', $css);
    }

    public function test_keeper_battlefield_tools_are_collapsed_into_one_disclosure_and_lens_help_is_removed(): void
    {
        $view = $this->source('app/Tabletop/Views/chamber.php');
        self::assertStringContainsString('class="gmrt-keeper-controls" data-keeper-controls', $view);
        self::assertStringContainsString('<summary>Dungeon Master Controls</summary>', $view);
        self::assertStringContainsString("The Keeper's Lantern Rack", $view);
        self::assertStringContainsString('Sight Beyond the Door', $view);
        self::assertStringNotContainsString('data-cartographers-lens', $view);
    }

    public function test_dungeon_master_character_picker_is_kept_available_but_compact(): void
    {
        $view = $this->source('app/Tabletop/Views/chamber.php');
        self::assertStringContainsString('class="gmrt-character-gate-disclosure"', $view);
        self::assertStringContainsString('<summary>Choose a Test Adventurer</summary>', $view);
        self::assertStringContainsString('Choose Your Adventurer', $view);
    }
}
