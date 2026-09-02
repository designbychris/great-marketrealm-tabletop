<?php

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Presentation;

use PHPUnit\Framework\TestCase;

final class KeeperRearrangesFurnitureRegressionTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        $this->root = dirname(__DIR__, 4);
    }

    public function test_keeper_drawers_share_a_moving_rail(): void
    {
        $css = file_get_contents($this->root . '/assets/css/tabletop.css');
        $js = file_get_contents($this->root . '/assets/js/tabletop.js');

        self::assertStringContainsString('data-keeper-drawer-open="atlas"', $css);
        self::assertStringContainsString('data-keeper-drawer-open="bestiary"', $css);
        self::assertStringContainsString("root.dataset.keeperDrawerOpen = open ? 'atlas' : '';", $js);
        self::assertStringContainsString("root.dataset.keeperDrawerOpen = open ? 'bestiary' : '';", $js);
    }

    public function test_table_command_header_carries_scene_and_mode_identity(): void
    {
        $view = file_get_contents($this->root . '/app/Tabletop/Views/chamber.php');
        $css = file_get_contents($this->root . '/assets/css/tabletop.css');

        self::assertStringContainsString('gmrt-table-command__scene', $view);
        self::assertStringContainsString('Current Tabletop Scene', $view);
        self::assertStringContainsString("\$encounter !== null ? 'Battle' : 'Exploration Mode'", $view);
        self::assertStringContainsString('--gmrt-command-width: 1650px', $css);
    }

    public function test_board_scene_heading_is_retained_for_accessibility_but_visually_compacted(): void
    {
        $view = file_get_contents($this->root . '/app/Tabletop/Views/chamber.php');
        $css = file_get_contents($this->root . '/assets/css/tabletop.css');

        self::assertStringContainsString('id="gmrt-scene-title"', $view);
        self::assertStringContainsString('.gmrt-board__heading > div:first-child', $css);
        self::assertStringContainsString('clip: rect(0,0,0,0)', $css);
    }

    public function test_gathering_uses_character_seat_portrait_or_player_placeholder(): void
    {
        $view = file_get_contents($this->root . '/app/Tabletop/Views/chamber.php');

        self::assertStringContainsString('gmrt-party__role gmrt-party__seat', $view);
        self::assertStringContainsString('$memberCompanionToken', $view);
        self::assertStringContainsString('Playing: ', $view);
        self::assertStringContainsString('Player — no character selected', $view);
        self::assertStringContainsString('<span aria-hidden="true">P</span>', $view);
        self::assertStringContainsString('<span aria-hidden="true">DM</span>', $view);
    }

    public function test_gathering_management_and_vitality_have_separate_visual_bands(): void
    {
        $css = file_get_contents($this->root . '/assets/css/tabletop.css');

        self::assertStringContainsString('.gmrt-party__seat {', $css);
        self::assertStringContainsString('.gmrt-party__remove {', $css);
        self::assertStringContainsString('border-top: 1px solid rgba(216,173,79,.22)', $css);
    }
}
