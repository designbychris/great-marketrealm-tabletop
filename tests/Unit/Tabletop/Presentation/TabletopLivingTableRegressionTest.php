<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Presentation;

use PHPUnit\Framework\TestCase;

final class TabletopLivingTableRegressionTest extends TestCase
{
    public function testViewExposesTokenRevisionAndKeyboardSelectionSurface(): void
    {
        $source = (string) file_get_contents(
            dirname(__DIR__, 4)
                . '/app/Tabletop/Views/chamber.php'
        );

        self::assertStringContainsString(
            'data-token-revision',
            $source
        );
        self::assertStringContainsString(
            'tabindex="0"',
            $source
        );
        self::assertStringContainsString(
            'role="button"',
            $source
        );
        self::assertStringContainsString(
            'aria-live="polite"',
            $source
        );
    }

    public function testClientUsesServerAuthoritativeAjaxMovementAndRefresh(): void
    {
        $source = (string) file_get_contents(
            dirname(__DIR__, 4)
                . '/assets/js/tabletop.js'
        );

        self::assertStringContainsString(
            "request('gmrt_move_token'",
            $source
        );
        self::assertStringContainsString(
            "request('gmrt_tabletop_state'",
            $source
        );
        self::assertStringContainsString(
            'setInterval(refresh, 5000)',
            $source
        );
        self::assertStringContainsString(
            "event.key === 'ArrowLeft'",
            $source
        );
    }

    public function testClientDoesNotContainWebsocketDependencyYet(): void
    {
        $source = strtolower(
            (string) file_get_contents(
                dirname(__DIR__, 4)
                    . '/assets/js/tabletop.js'
            )
        );

        self::assertStringNotContainsString(
            'websocket',
            $source
        );
        self::assertStringNotContainsString(
            'socket.io',
            $source
        );
    }
}
