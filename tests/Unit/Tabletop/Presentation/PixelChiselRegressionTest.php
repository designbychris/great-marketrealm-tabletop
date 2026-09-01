<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Presentation;

use PHPUnit\Framework\TestCase;

final class PixelChiselRegressionTest extends TestCase
{
    private function source(string $path): string
    {
        return (string) file_get_contents(dirname(__DIR__, 4) . '/' . $path);
    }

    public function test_pixel_chisel_defines_a_reusable_tabletop_palette(): void
    {
        $css = $this->source('assets/css/tabletop.css');

        self::assertStringContainsString('Phase IV.32.1 — The Keeper Finds the Pixel Chisel', $css);
        self::assertStringContainsString('--gmrt-pixel-ink:', $css);
        self::assertStringContainsString('--gmrt-pixel-gold:', $css);
        self::assertStringContainsString('--gmrt-pixel-focus:', $css);
        self::assertStringContainsString('--gmrt-pixel-shadow:', $css);
    }

    public function test_shell_panels_and_battlefield_frame_use_pixel_geometry(): void
    {
        $css = $this->source('assets/css/tabletop.css');

        self::assertStringContainsString('.gmrt-chamber__masthead {', $css);
        self::assertStringContainsString('.gmrt-board,', $css);
        self::assertStringContainsString('.gmrt-board__lens-stage {', $css);
        self::assertStringContainsString('border-radius: 2px;', $css);
        self::assertStringContainsString('var(--gmrt-pixel-shadow)', $css);
    }

    public function test_common_controls_gain_pressed_focus_and_disabled_states(): void
    {
        $css = $this->source('assets/css/tabletop.css');

        self::assertStringContainsString('.gmrt-chamber button:not(.gmrt-token):active:not(:disabled)', $css);
        self::assertStringContainsString('transform: translate(2px, 2px);', $css);
        self::assertStringContainsString('.gmrt-chamber button:not(.gmrt-token):focus-visible', $css);
        self::assertStringContainsString('outline: 2px solid var(--gmrt-pixel-focus);', $css);
        self::assertStringContainsString('.gmrt-chamber button:not(.gmrt-token):disabled', $css);
    }

    public function test_lens_controls_receive_the_first_pixel_toolkit_treatment(): void
    {
        $css = $this->source('assets/css/tabletop.css');

        self::assertStringContainsString('.gmrt-board__lens-controls {', $css);
        self::assertStringContainsString('backdrop-filter: none;', $css);
        self::assertStringContainsString('.gmrt-board__lens-controls output {', $css);
    }

    public function test_pixel_chisel_remains_a_presentation_only_phase(): void
    {
        $phase = $this->source('docs/Roadmap/PHASE-IV.32.1.md');

        self::assertStringContainsString('The Pixel Chisel is presentation-only.', $phase);
        self::assertStringContainsString('does not alter encounter state', $phase);
        self::assertStringContainsString('prefers-reduced-motion', $phase);
    }
}
