<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Presentation;

use PHPUnit\Framework\TestCase;

final class ChronicleLayoutRegressionTest extends TestCase
{
    public function testPartyMemberStylesAreScopedAwayFromChronicleItems(): void
    {
        $css = (string) file_get_contents(
            dirname(__DIR__, 4)
                . '/assets/css/tabletop.css'
        );

        self::assertStringContainsString(
            '.gmrt-party > ul > li {',
            $css
        );
        self::assertStringNotContainsString(
            "\n.gmrt-party li {\n",
            $css
        );
    }

    public function testChronicleRowsGrowWithTheirContent(): void
    {
        $css = (string) file_get_contents(
            dirname(__DIR__, 4)
                . '/assets/css/tabletop.css'
        );

        self::assertStringContainsString(
            'grid-auto-rows: max-content;',
            $css
        );
        self::assertStringContainsString(
            'height: auto;',
            $css
        );
        self::assertStringContainsString(
            'overflow-wrap: anywhere;',
            $css
        );
    }

    public function testChronicleKeepsReadableScrollableHeight(): void
    {
        $css = (string) file_get_contents(
            dirname(__DIR__, 4)
                . '/assets/css/tabletop.css'
        );

        self::assertStringContainsString(
            'min-height: 13rem;',
            $css
        );
        self::assertStringContainsString(
            'max-height: 26rem;',
            $css
        );
        self::assertStringContainsString(
            'scrollbar-gutter: stable;',
            $css
        );
    }
}
