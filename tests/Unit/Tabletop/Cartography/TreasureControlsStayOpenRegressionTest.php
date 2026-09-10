<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class TreasureControlsStayOpenRegressionTest extends TestCase
{
    private function root(string $path): string
    {
        return dirname(__DIR__, 4) . '/' . $path;
    }

    public function test_rendered_and_live_forge_revisions_both_include_treasure(): void
    {
        $view = (string) file_get_contents($this->root('app/Tabletop/Views/chamber.php'));
        $ajax = (string) file_get_contents($this->root('app/Tabletop/Http/TabletopAjaxController.php'));

        self::assertStringContainsString("'treasure' => \$dungeonForge['treasure'] ?? []", $view);
        self::assertStringContainsString("'treasure' => \$forge['treasure'] ?? []", $ajax);
    }

    public function test_player_render_boundary_filters_unrevealed_treasure(): void
    {
        $view = (string) file_get_contents($this->root('app/Tabletop/Views/chamber.php'));

        self::assertStringContainsString("\$dungeonForge['treasure']=array_values(array_filter", $view);
        self::assertStringContainsString("!empty(\$treasure['revealed'])", $view);
    }

    public function test_live_fragment_replacement_preserves_open_dm_controls(): void
    {
        $javascript = (string) file_get_contents($this->root('assets/js/tabletop.js'));

        self::assertStringContainsString('keeperControlsWasOpen', $javascript);
        self::assertStringContainsString('incomingKeeperControls.open = true', $javascript);
    }

    public function test_live_state_guard_still_requires_nonce(): void
    {
        $ajax = (string) file_get_contents($this->root('app/Tabletop/Http/TabletopAjaxController.php'));

        self::assertStringContainsString('check_ajax_referer(', $ajax);
        self::assertStringContainsString('self::NONCE_ACTION', $ajax);
    }
}
