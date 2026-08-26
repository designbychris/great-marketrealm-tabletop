<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Http;

use PHPUnit\Framework\TestCase;

final class TabletopAjaxRegressionTest extends TestCase
{
    private string $source;

    protected function setUp(): void
    {
        $this->source = (string) file_get_contents(
            dirname(__DIR__, 4)
                . '/app/Tabletop/Http/TabletopAjaxController.php'
        );
    }

    public function testMovementEndpointRequiresNonceAndAuthentication(): void
    {
        self::assertStringContainsString(
            'is_user_logged_in()',
            $this->source
        );
        self::assertStringContainsString(
            'check_ajax_referer(',
            $this->source
        );
        self::assertStringContainsString(
            "NONCE_ACTION = 'gmrt_tabletop_state'",
            $this->source
        );
    }

    public function testMovementEndpointDistinguishesConflictAndPermissionErrors(): void
    {
        self::assertStringContainsString(
            'StaleTokenRevision',
            $this->source
        );
        self::assertStringContainsString(
            '409',
            $this->source
        );
        self::assertStringContainsString(
            '403',
            $this->source
        );
    }
}
