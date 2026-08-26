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
}
