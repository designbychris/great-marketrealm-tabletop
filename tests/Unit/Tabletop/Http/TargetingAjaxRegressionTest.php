<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Http;

use PHPUnit\Framework\TestCase;

final class TargetingAjaxRegressionTest extends TestCase
{
    public function testTargetMeasureEndpointIsAuthenticatedAndNonceProtected(): void
    {
        $source = (string) file_get_contents(
            dirname(__DIR__, 4)
                . '/app/Tabletop/Http/TargetingAjaxController.php'
        );

        self::assertStringContainsString(
            'is_user_logged_in()',
            $source
        );
        self::assertStringContainsString(
            'check_ajax_referer(',
            $source
        );
    }

    public function testProviderRegistersOnlyAuthenticatedTargetMeasureEndpoint(): void
    {
        $source = (string) file_get_contents(
            dirname(__DIR__, 4)
                . '/app/Tabletop/TabletopServiceProvider.php'
        );

        self::assertStringContainsString(
            "'wp_ajax_gmrt_measure_target'",
            $source
        );
        self::assertStringNotContainsString(
            "'wp_ajax_nopriv_gmrt_measure_target'",
            $source
        );
    }
}
