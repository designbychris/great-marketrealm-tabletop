<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Http;

use PHPUnit\Framework\TestCase;

final class EncounterAjaxRegressionTest extends TestCase
{
    public function testEncounterControlUsesAuthenticatedNonceProtectedEndpoints(): void
    {
        $source = (string) file_get_contents(
            dirname(__DIR__, 4)
                . '/app/Tabletop/Http/EncounterAjaxController.php'
        );

        self::assertStringContainsString('is_user_logged_in()', $source);
        self::assertStringContainsString('check_ajax_referer(', $source);
        self::assertStringContainsString('get_current_user_id()', $source);
        self::assertStringContainsString('StaleEncounterRevision', $source);
        self::assertStringContainsString('409', $source);
        self::assertStringContainsString('403', $source);
    }
}
