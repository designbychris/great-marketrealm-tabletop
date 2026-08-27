<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Presentation;

use PHPUnit\Framework\TestCase;

final class BattlefieldTargetingViewRegressionTest extends TestCase
{
    public function testBoardContainsTargetingLineLayer(): void
    {
        $view = (string) file_get_contents(
            dirname(__DIR__, 4)
                . '/app/Tabletop/Views/chamber.php'
        );

        self::assertStringContainsString(
            'data-target-line',
            $view
        );
        self::assertStringContainsString(
            'data-target-range-status',
            $view
        );
    }

    public function testClientRequestsAuthoritativeTargetMeasure(): void
    {
        $script = (string) file_get_contents(
            dirname(__DIR__, 4)
                . '/assets/js/tabletop.js'
        );

        self::assertStringContainsString(
            "'gmrt_measure_target'",
            $script
        );
        self::assertStringContainsString(
            "'OUT OF RANGE'",
            $script
        );
        self::assertStringContainsString(
            'drawTargetLine(',
            $script
        );
    }
}
