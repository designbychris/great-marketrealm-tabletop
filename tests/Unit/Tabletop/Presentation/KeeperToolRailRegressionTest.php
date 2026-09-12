<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Presentation;

use PHPUnit\Framework\TestCase;

final class KeeperToolRailRegressionTest extends TestCase
{
    private function source(string $path): string
    {
        return (string) file_get_contents(dirname(__DIR__, 4) . '/' . $path);
    }

    public function test_tools_atlas_and_bestiary_remain_three_separate_keeper_drawers(): void
    {
        $view = $this->source('app/Tabletop/Views/chamber.php');

        self::assertStringContainsString('data-keeper-tools data-open="false"', $view);
        self::assertStringContainsString('data-keepers-atlas data-open="false"', $view);
        self::assertStringContainsString('data-keepers-bestiary data-open="false"', $view);
        self::assertStringContainsString('data-keeper-tools-toggle', $view);
        self::assertStringContainsString('data-atlas-toggle', $view);
        self::assertStringContainsString('data-bestiary-toggle', $view);
        self::assertStringContainsString('aria-controls="gmrt-keeper-tools-panel"', $view);
        self::assertStringContainsString('aria-controls="gmrt-keepers-atlas-panel"', $view);
        self::assertStringContainsString('aria-controls="gmrt-keepers-bestiary-panel"', $view);
    }

    public function test_tool_drawer_owns_the_existing_battlefield_controls_without_moving_atlas_or_bestiary_into_it(): void
    {
        $view = $this->source('app/Tabletop/Views/chamber.php');
        $tools = strpos($view, 'data-keeper-tools data-open="false"');
        $atlas = strpos($view, 'data-keepers-atlas data-open="false"');
        $bestiary = strpos($view, 'data-keepers-bestiary data-open="false"');
        $battlemap = strpos($view, 'data-choose-battlemap');
        $fog = strpos($view, 'data-fog-enabled');
        $lantern = strpos($view, 'data-lantern-rack');
        $furniture = strpos($view, 'data-furniture-palette');

        self::assertNotFalse($tools);
        self::assertNotFalse($atlas);
        self::assertNotFalse($bestiary);
        self::assertNotFalse($battlemap);
        self::assertNotFalse($fog);
        self::assertNotFalse($lantern);
        self::assertNotFalse($furniture);
        self::assertLessThan($battlemap, $tools);
        self::assertLessThan($fog, $tools);
        self::assertLessThan($lantern, $tools);
        self::assertLessThan($furniture, $tools);
        self::assertLessThan($tools, $atlas);
        self::assertLessThan($tools, $bestiary);
    }

    public function test_opening_one_keeper_drawer_closes_the_other_two(): void
    {
        $js = $this->source('assets/js/tabletop.js');

        self::assertStringContainsString("tools: {", $js);
        self::assertStringContainsString("atlas: {", $js);
        self::assertStringContainsString("bestiary: {", $js);
        self::assertStringContainsString('Object.entries(drawers).forEach', $js);
        self::assertStringContainsString('const candidateOpen = open && candidateKind === kind;', $js);
        self::assertStringContainsString("candidate.drawer.dataset.open = candidateOpen ? 'true' : 'false';", $js);
        self::assertStringContainsString("root.dataset.keeperDrawerOpen = open ? kind : '';", $js);
    }

    public function test_active_drawer_is_restored_after_live_chamber_replacement(): void
    {
        $js = $this->source('assets/js/tabletop.js');

        self::assertStringContainsString("const keeperDrawerWasOpen = current?.dataset.keeperDrawerOpen || '';", $js);
        self::assertStringContainsString("['tools', 'atlas', 'bestiary'].includes(keeperDrawerWasOpen)", $js);
        self::assertStringContainsString('setKeeperDrawerOpen(keeperDrawerWasOpen, true);', $js);
    }

    public function test_rail_presentation_has_one_compact_three_tab_rail_and_independent_drawers(): void
    {
        $view = $this->source('app/Tabletop/Views/chamber.php');
        $css = $this->source('assets/css/tabletop.css');

        self::assertStringContainsString('class="gmrt-keeper-rail"', $view);
        self::assertStringContainsString('gmrt-keeper-rail__tab--tools', $view);
        self::assertStringContainsString('gmrt-keeper-rail__tab--atlas', $view);
        self::assertStringContainsString('gmrt-keeper-rail__tab--bestiary', $view);
        self::assertStringContainsString('--gmrt-keeper-rail-top: 9rem;', $css);
        self::assertStringContainsString('gap: .28rem;', $css);
        self::assertStringContainsString('.gmrt-keeper-rail__tab--bestiary {', $css);
        self::assertStringContainsString('min-height: 6.35rem;', $css);
        self::assertStringContainsString('right: var(--gmrt-keeper-drawer-width);', $css);
        self::assertStringContainsString('.gmrt-keeper-tools-drawer[data-open="true"],', $css);
        self::assertStringContainsString('transform: translateX(100%);', $css);
    }
}
