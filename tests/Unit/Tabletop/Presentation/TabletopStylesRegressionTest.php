<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Presentation;

use PHPUnit\Framework\TestCase;

final class TabletopStylesRegressionTest extends TestCase
{
    public function testTabletopStylesIncludeGridTokenAndReducedMotionRules(): void
    {
        $source = (string) file_get_contents(
            dirname(__DIR__, 4)
                . '/assets/css/tabletop.css'
        );

        self::assertStringContainsString(
            '.gmrt-board__grid',
            $source
        );
        self::assertStringContainsString(
            '.gmrt-token',
            $source
        );
        self::assertStringContainsString(
            '@media (prefers-reduced-motion: reduce)',
            $source
        );
    }
}
