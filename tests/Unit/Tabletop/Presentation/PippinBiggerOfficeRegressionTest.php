<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Presentation;

use PHPUnit\Framework\TestCase;

final class PippinBiggerOfficeRegressionTest extends TestCase
{
    private function stylesheet(): string
    {
        $css = file_get_contents(dirname(__DIR__, 4) . '/assets/css/tabletop.css');
        self::assertIsString($css);

        return $css;
    }

    public function test_keeper_drawers_share_one_wider_responsive_workspace_width(): void
    {
        $css = $this->stylesheet();

        self::assertStringContainsString('Phase IV.32.2B — Pippin Demands a Bigger Office.', $css);
        self::assertStringContainsString('--gmrt-keeper-drawer-width: 700px;', $css);
        self::assertStringContainsString('.gmrt-atlas-drawer__panel,', $css);
        self::assertStringContainsString('.gmrt-bestiary-drawer__panel {', $css);
        self::assertStringContainsString('width: min(var(--gmrt-keeper-drawer-width), calc(100vw - 4rem));', $css);
    }

    public function test_atlas_and_bestiary_registers_gain_breathing_room_without_horizontal_scroll(): void
    {
        $css = $this->stylesheet();

        self::assertStringContainsString('.gmrt-atlas__register,', $css);
        self::assertStringContainsString('.gmrt-bestiary__register {', $css);
        self::assertStringContainsString('grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));', $css);
        self::assertStringContainsString('@media (max-width: 760px)', $css);
        self::assertStringContainsString('grid-template-columns: 1fr;', $css);
    }

    public function test_pippin_field_note_uses_the_larger_office_for_a_readable_portrait(): void
    {
        $css = $this->stylesheet();

        self::assertStringContainsString('grid-template-columns: 124px minmax(0, 1fr);', $css);
        self::assertStringContainsString('width: 142px;', $css);
        self::assertStringContainsString('min-height: 132px;', $css);
    }

    public function test_gathering_rail_has_elbow_room_and_structured_member_cards(): void
    {
        $css = $this->stylesheet();

        self::assertStringContainsString('--gmrt-party-rail-width: 370px;', $css);
        self::assertStringContainsString('grid-template-columns: minmax(0, 1fr) minmax(340px, var(--gmrt-party-rail-width));', $css);
        self::assertStringContainsString('grid-template-columns: 54px minmax(0, 1fr) auto;', $css);
        self::assertStringContainsString('.gmrt-party__member > .gmrt-hp {', $css);
        self::assertStringContainsString('grid-column: 1 / -1;', $css);
    }

    public function test_fellowship_ribbon_and_member_actions_fit_the_pixel_rail(): void
    {
        $css = $this->stylesheet();

        self::assertStringContainsString('grid-template-columns: repeat(8, 1fr);', $css);
        self::assertStringContainsString('.gmrt-party__remove {', $css);
        self::assertStringContainsString('grid-column: 2 / -1;', $css);
        self::assertStringContainsString('width: 100%;', $css);
    }
}
