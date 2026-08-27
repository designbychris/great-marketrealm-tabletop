<?php
declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class CartographyWiringRegressionTest extends TestCase
{
    public function testReplaceBattlemapAjaxRouteIsRegistered(): void
    {
        $source = (string) file_get_contents(
            dirname(__DIR__, 4)
            . '/app/Tabletop/TabletopServiceProvider.php'
        );

        self::assertStringContainsString(
            "'wp_ajax_gmrt_replace_battlemap'",
            $source
        );
        self::assertStringContainsString(
            "CartographersTableFactory::make()",
            $source
        );
    }
}
