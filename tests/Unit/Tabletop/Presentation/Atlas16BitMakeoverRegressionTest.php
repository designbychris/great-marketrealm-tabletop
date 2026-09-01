<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Presentation;

use PHPUnit\Framework\TestCase;

final class Atlas16BitMakeoverRegressionTest extends TestCase
{
    private function stylesheet(): string
    {
        $css = file_get_contents(dirname(__DIR__, 4) . '/assets/css/tabletop.css');
        self::assertIsString($css);

        return $css;
    }

    public function test_atlas_drawer_uses_the_pixel_chisel_vocabulary(): void
    {
        $css = $this->stylesheet();

        self::assertStringContainsString('Phase IV.32.2 — The Atlas Gets a 16-Bit Makeover.', $css);
        self::assertStringContainsString('.gmrt-atlas-drawer__toggle::before', $css);
        self::assertStringContainsString('.gmrt-atlas-drawer__panel::-webkit-scrollbar-thumb', $css);
        self::assertStringContainsString('var(--gmrt-pixel-gold-hi)', $css);
    }

    public function test_scene_cards_have_pixel_active_and_forged_states(): void
    {
        $css = $this->stylesheet();

        self::assertStringContainsString('.gmrt-atlas-card.is-active::before', $css);
        self::assertStringContainsString('.gmrt-atlas-card.is-forged-world::after', $css);
        self::assertStringContainsString('content: "FORGED";', $css);
        self::assertStringContainsString('.gmrt-atlas-card__actions', $css);
    }

    public function test_keeper_workbench_tools_share_one_pixel_treatment(): void
    {
        $css = $this->stylesheet();

        self::assertStringContainsString('.gmrt-atlas-forge,', $css);
        self::assertStringContainsString('.gmrt-threshold-tools,', $css);
        self::assertStringContainsString('.gmrt-cartography-assistant,', $css);
        self::assertStringContainsString('.gmrt-lantern-rack,', $css);
        self::assertStringContainsString('.gmrt-dungeon-forge {', $css);
    }

    public function test_collapsible_keeper_tools_use_pixel_disclosure_markers(): void
    {
        $css = $this->stylesheet();

        self::assertStringContainsString('.gmrt-atlas-forge > summary::before', $css);
        self::assertStringContainsString('content: "▶";', $css);
        self::assertStringContainsString('.gmrt-atlas-forge[open] > summary::before', $css);
        self::assertStringContainsString('content: "▼";', $css);
    }

    public function test_lantern_rack_preserves_clear_lit_and_doused_states(): void
    {
        $css = $this->stylesheet();

        self::assertStringContainsString('.gmrt-lantern-rack button.is-active', $css);
        self::assertStringContainsString('.gmrt-lantern-rack__state.is-lit', $css);
        self::assertStringContainsString('.gmrt-lantern-rack__state.is-doused', $css);
    }
}
