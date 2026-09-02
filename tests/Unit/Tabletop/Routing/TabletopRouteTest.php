<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Routing;

use PHPUnit\Framework\TestCase;

final class TabletopRouteTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        $this->root = dirname(__DIR__, 4);
    }

    public function testProviderDoesNotClaimWordPressPageRouteBeyondDoorLogin(): void
    {
        $source = (string) file_get_contents(
            $this->root
                . '/app/Tabletop/TabletopServiceProvider.php'
        );

        self::assertStringNotContainsString(
            'TabletopRoute',
            $source
        );
        self::assertStringContainsString(
            "'template_redirect'",
            $source
        );
        self::assertStringContainsString(
            "[\$this->shortcode, 'handleDoorLogin']",
            $source
        );
        self::assertStringNotContainsString(
            'add_rewrite_rule',
            $source
        );
    }

    public function testProviderKeepsLivingTableAjaxActions(): void
    {
        $source = (string) file_get_contents(
            $this->root
                . '/app/Tabletop/TabletopServiceProvider.php'
        );

        self::assertStringContainsString(
            "'wp_ajax_gmrt_tabletop_state'",
            $source
        );
        self::assertStringContainsString(
            "'wp_ajax_gmrt_move_token'",
            $source
        );
    }

    public function testProviderKeepsEncounterControlActions(): void
    {
        $source = (string) file_get_contents(
            $this->root
                . '/app/Tabletop/TabletopServiceProvider.php'
        );

        self::assertStringContainsString(
            "'wp_ajax_gmrt_prepare_encounter'",
            $source
        );
        self::assertStringContainsString(
            "'wp_ajax_gmrt_advance_encounter'",
            $source
        );
        self::assertStringContainsString(
            "'wp_ajax_gmrt_end_encounter'",
            $source
        );
    }
}
