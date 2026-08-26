<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Http;

use PHPUnit\Framework\TestCase;

final class DeathSaveAjaxRegressionTest extends TestCase
{
    public function testDeathSaveEndpointIsAuthenticatedAndNonceProtected(): void
    {
        $source = (string) file_get_contents(
            dirname(__DIR__, 4)
                . '/app/Tabletop/Http/DeathSaveAjaxController.php'
        );

        self::assertStringContainsString('is_user_logged_in()', $source);
        self::assertStringContainsString('check_ajax_referer(', $source);
        self::assertStringContainsString('get_current_user_id()', $source);
        self::assertStringContainsString("'death_save' =>", $source);
    }
}
