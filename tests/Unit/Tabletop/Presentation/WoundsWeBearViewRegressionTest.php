<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Presentation;

use PHPUnit\Framework\TestCase;

final class WoundsWeBearViewRegressionTest extends TestCase
{
    public function testClientAnnouncesDefenseReactions(): void
    {
        $source = (string) file_get_contents(
            dirname(__DIR__, 4)
                . '/assets/js/tabletop.js'
        );

        self::assertStringContainsString("' RESIST!'", $source);
        self::assertStringContainsString("' WEAK!'", $source);
        self::assertStringContainsString("' IMMUNE!'", $source);
    }

    public function testClientShowsResolvedAndRawDamageWhenAdjusted(): void
    {
        $source = (string) file_get_contents(
            dirname(__DIR__, 4)
                . '/assets/js/tabletop.js'
        );

        self::assertStringContainsString(
            'adjusted.resolved_damage',
            $source
        );
        self::assertStringContainsString(
            'adjusted.raw_damage',
            $source
        );
    }
}
