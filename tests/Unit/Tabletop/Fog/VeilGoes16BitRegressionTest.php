<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Fog;

use PHPUnit\Framework\TestCase;

final class VeilGoes16BitRegressionTest extends TestCase
{
    private function source(string $path): string
    {
        return (string) file_get_contents(dirname(__DIR__, 4) . '/' . $path);
    }

    public function testUnknownAndRememberedFogKeepTheirExistingAuthorityClasses(): void
    {
        $js = $this->source('assets/js/tabletop.js');

        self::assertStringContainsString("'gmrt-fog-cell is-memory'", $js);
        self::assertStringContainsString("'gmrt-fog-cell is-unexplored'", $js);
        self::assertStringContainsString('visible.has(key)', $js);
    }

    public function testSixteenBitVeilDistinguishesUnknownMemoryAndSightFrontier(): void
    {
        $css = $this->source('assets/css/tabletop.css');

        self::assertStringContainsString('Phase IV.32.4B — The Veil Goes 16-Bit', $css);
        self::assertStringContainsString('.gmrt-fog-cell.is-unexplored::before', $css);
        self::assertStringContainsString('.gmrt-fog-cell.is-memory::before', $css);
        self::assertStringContainsString('.gmrt-fog-cell.is-vision-edge::after', $css);
        self::assertStringContainsString('.gmrt-fog-cell.is-memory-edge::after', $css);
    }

    public function testKeeperPlayerFogPreviewIsPresentationOnly(): void
    {
        $js = $this->source('assets/js/tabletop.js');
        $css = $this->source('assets/css/tabletop.css');

        self::assertStringContainsString("'is-player-preview'", $js);
        self::assertStringContainsString('enabled && dmBypass && preview', $js);
        self::assertStringContainsString('.gmrt-fog-layer.is-player-preview', $css);
        self::assertStringContainsString('PLAYER VEIL PREVIEW', $css);
    }

    public function testVeilPixelPassRespectsReducedMotion(): void
    {
        $css = $this->source('assets/css/tabletop.css');

        self::assertStringContainsString('@media (prefers-reduced-motion: reduce)', $css);
        self::assertStringContainsString('transition: none;', $css);
    }
}
