<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Presentation;

use PHPUnit\Framework\TestCase;

final class KeeperLightsPixelsRegressionTest extends TestCase
{
    private function source(string $path): string
    {
        return (string) file_get_contents(dirname(__DIR__, 4) . '/' . $path);
    }

    public function test_keeper_environmental_markers_are_css_drawn_pixel_objects(): void
    {
        $js = $this->source('assets/js/tabletop.js');

        self::assertStringContainsString("marker.className = 'gmrt-keeper-light-marker is-' + lightKind", $js);
        self::assertStringContainsString("glow.dataset.lightKind = lightKind", $js);
        self::assertStringContainsString("marker.setAttribute('aria-hidden', 'true')", $js);

        $renderStart = strpos($js, 'const renderLightSources =');
        $renderEnd = strpos($js, 'const renderFootsteps', $renderStart + 1);
        if ($renderEnd === false) {
            $renderEnd = strpos($js, 'const ', $renderStart + strlen('const renderLightSources ='));
        }

        self::assertNotFalse($renderStart);
        self::assertNotFalse($renderEnd);

        $renderLightSources = substr($js, $renderStart, $renderEnd - $renderStart);

        self::assertStringNotContainsString('marker.textContent', $renderLightSources);
        self::assertStringNotContainsString('🔥', $renderLightSources);
    }

    public function test_each_keeper_light_keeps_a_distinct_16_bit_motion_personality(): void
    {
        $css = $this->source('assets/css/tabletop.css');

        self::assertStringContainsString('@keyframes gmrt-keeper-torch-flicker', $css);
        self::assertStringContainsString('@keyframes gmrt-keeper-candle-flutter', $css);
        self::assertStringContainsString('@keyframes gmrt-keeper-lantern-breathe', $css);
        self::assertStringContainsString('@keyframes gmrt-keeper-brazier-pulse', $css);
        self::assertStringContainsString('@keyframes gmrt-keeper-magic-shimmer', $css);
        self::assertStringContainsString('steps(3, end) infinite', $css);
    }

    public function test_doused_keeper_lights_are_static_and_visibly_dormant(): void
    {
        $css = $this->source('assets/css/tabletop.css');

        self::assertStringContainsString('.gmrt-carried-light.is-environmental.is-doused::after { display: none; }', $css);
        self::assertStringContainsString('grayscale(1) brightness(.65)', $css);
    }

    public function test_light_motion_respects_reduced_motion_without_hiding_the_light(): void
    {
        $css = $this->source('assets/css/tabletop.css');

        self::assertStringContainsString('@media (prefers-reduced-motion: reduce)', $css);
        self::assertStringContainsString('.gmrt-carried-light.is-environmental::after { animation: none !important; opacity: .48; filter: none; }', $css);
    }

    public function test_phase_does_not_change_certified_keeper_light_radii(): void
    {
        $controller = $this->source('app/Tabletop/Http/EnvironmentalLightAjaxController.php');

        self::assertStringContainsString("'torch' => ['label' => 'Torch', 'bright' => 20, 'dim' => 20]", $controller);
        self::assertStringContainsString("'lantern' => ['label' => 'Lantern', 'bright' => 30, 'dim' => 30]", $controller);
        self::assertStringContainsString("'brazier' => ['label' => 'Brazier', 'bright' => 60, 'dim' => 60]", $controller);
        self::assertStringContainsString("'candle' => ['label' => 'Candle', 'bright' => 10, 'dim' => 10]", $controller);
        self::assertStringContainsString("'magical' => ['label' => 'Magical Light', 'bright' => 40, 'dim' => 40]", $controller);
    }
}
