<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Presentation;

use PHPUnit\Framework\TestCase;

final class AfflictionMechanicsViewRegressionTest extends TestCase
{
    public function testBrowserShowsRollModeAndBothD20s(): void
    {
        $source = (string) file_get_contents(
            dirname(__DIR__, 4)
                . '/assets/js/tabletop.js'
        );

        self::assertStringContainsString(
            'attack.roll_mode.toUpperCase()',
            $source
        );
        self::assertStringContainsString(
            "attack.rolls.join(' / ')",
            $source
        );
    }
}
