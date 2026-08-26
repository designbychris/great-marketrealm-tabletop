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

    public function testRouteRegistersBareTabletopPath(): void
    {
        $source = file_get_contents(
            $this->root
                . '/app/Tabletop/Routing/TabletopRoute.php'
        );

        self::assertIsString($source);
        self::assertStringContainsString(
            "'^tabletop/?$'",
            $source
        );
    }

    public function testRouteSupportsTableSpecificPath(): void
    {
        $source = file_get_contents(
            $this->root
                . '/app/Tabletop/Routing/TabletopRoute.php'
        );

        self::assertIsString($source);
        self::assertStringContainsString(
            "'^tabletop/([^/]+)/?$'",
            $source
        );
        self::assertStringContainsString(
            "public const TABLE_VAR = 'gmrt_table'",
            $source
        );
    }

    public function testVisibleShellUsesTemplateRedirect(): void
    {
        $source = file_get_contents(
            $this->root
                . '/app/Tabletop/TabletopServiceProvider.php'
        );

        self::assertIsString($source);
        self::assertStringContainsString(
            "'template_redirect'",
            $source
        );
        self::assertStringContainsString(
            "'query_vars'",
            $source
        );
    }

    public function testProviderRegistersAuthenticatedLivingTableAjaxActions(): void
    {
        $source = file_get_contents(
            $this->root
                . '/app/Tabletop/TabletopServiceProvider.php'
        );

        self::assertIsString($source);
        self::assertStringContainsString(
            "'wp_ajax_gmrt_tabletop_state'",
            $source
        );
        self::assertStringContainsString(
            "'wp_ajax_gmrt_move_token'",
            $source
        );
        self::assertStringNotContainsString(
            "'wp_ajax_nopriv_gmrt_move_token'",
            $source
        );
    }


    public function testProviderRegistersEncounterControlActions(): void
    {
        $source = file_get_contents(
            $this->root
                . '/app/Tabletop/TabletopServiceProvider.php'
        );

        self::assertIsString($source);
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
        self::assertStringNotContainsString(
            "'wp_ajax_nopriv_gmrt_prepare_encounter'",
            $source
        );
    }

}
