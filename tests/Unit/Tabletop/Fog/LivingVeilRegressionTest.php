<?php
declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Fog;

use PHPUnit\Framework\TestCase;

final class LivingVeilRegressionTest extends TestCase
{
    public function testClientMarksVisionAndMemoryEdges(): void
    {
        $source = (string) file_get_contents(
            dirname(__DIR__, 4)
            . '/assets/js/tabletop.js'
        );

        self::assertStringContainsString(
            "cell.classList.add('is-vision-edge')",
            $source
        );
        self::assertStringContainsString(
            "cell.classList.add('is-memory-edge')",
            $source
        );
        self::assertStringContainsString(
            'neighbours.some(',
            $source
        );
    }

    public function testUnexploredVeilUsesContinuousDarkSurface(): void
    {
        $source = (string) file_get_contents(
            dirname(__DIR__, 4)
            . '/assets/css/tabletop.css'
        );

        self::assertStringContainsString(
            '.gmrt-fog-cell.is-unexplored',
            $source
        );
        self::assertStringContainsString(
            'linear-gradient(',
            $source
        );
        self::assertStringNotContainsString(
            'radial-gradient(circle at 35% 30%',
            $source
        );
    }

    public function testRememberedTerrainIsDimmedRatherThanFullyHidden(): void
    {
        $source = (string) file_get_contents(
            dirname(__DIR__, 4)
            . '/assets/css/tabletop.css'
        );

        self::assertStringContainsString(
            '.gmrt-fog-cell.is-memory',
            $source
        );
        self::assertStringContainsString(
            'backdrop-filter: grayscale(.38) brightness(.72)',
            $source
        );
    }

    public function testVisionBoundaryUsesPixelDitherTreatment(): void
    {
        $source = (string) file_get_contents(
            dirname(__DIR__, 4)
            . '/assets/css/tabletop.css'
        );

        self::assertStringContainsString(
            '.gmrt-fog-cell.is-vision-edge::after',
            $source
        );
        self::assertStringContainsString(
            'repeating-linear-gradient(',
            $source
        );
    }

    public function testLivingVeilRespectsReducedMotion(): void
    {
        $source = (string) file_get_contents(
            dirname(__DIR__, 4)
            . '/assets/css/tabletop.css'
        );

        self::assertStringContainsString(
            '@media (prefers-reduced-motion: reduce)',
            $source
        );
        self::assertStringContainsString(
            'transition: none',
            $source
        );
    }
}
