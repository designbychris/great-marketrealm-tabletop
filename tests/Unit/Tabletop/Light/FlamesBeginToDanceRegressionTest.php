<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Light;

use PHPUnit\Framework\TestCase;

final class FlamesBeginToDanceRegressionTest extends TestCase
{
    private function root(string $path): string
    {
        return dirname(__DIR__, 4) . '/' . ltrim($path, '/');
    }

    public function test_keeper_environmental_markers_gain_pixel_emitter_particles(): void
    {
        $js = file_get_contents($this->root('assets/js/tabletop.js'));

        self::assertIsString($js);
        self::assertStringContainsString("marker.classList.add('is-dancing')", $js);
        self::assertStringContainsString("particle.className = 'gmrt-light-emitter-particle'", $js);
        self::assertStringContainsString("lightKind === 'brazier' ? 3", $js);
        self::assertStringContainsString("lightKind === 'torch' ? 2", $js);
        self::assertStringContainsString("if (source.lit !== false)", $js);
    }

    public function test_each_keeper_light_kind_has_a_distinct_dancing_treatment(): void
    {
        $css = file_get_contents($this->root('assets/css/tabletop.css'));

        self::assertIsString($css);
        self::assertStringContainsString('.gmrt-keeper-light-marker.is-dancing.is-torch', $css);
        self::assertStringContainsString('.gmrt-keeper-light-marker.is-dancing.is-candle', $css);
        self::assertStringContainsString('.gmrt-keeper-light-marker.is-dancing.is-lantern', $css);
        self::assertStringContainsString('.gmrt-keeper-light-marker.is-dancing.is-brazier', $css);
        self::assertStringContainsString('.gmrt-keeper-light-marker.is-dancing.is-magical', $css);
        self::assertStringContainsString('@keyframes gmrt-emitter-torch-dance', $css);
        self::assertStringContainsString('@keyframes gmrt-emitter-brazier-dance', $css);
        self::assertStringContainsString('@keyframes gmrt-emitter-magic-hover', $css);
    }

    public function test_visual_light_breathing_is_deliberately_small_and_presentation_only(): void
    {
        $css = file_get_contents($this->root('assets/css/tabletop.css'));
        $js = file_get_contents($this->root('assets/js/tabletop.js'));

        self::assertIsString($css);
        self::assertIsString($js);
        self::assertStringContainsString('scale(1.025)', $css);
        self::assertStringContainsString('scale(1.035)', $css);
        self::assertStringContainsString('@keyframes gmrt-emitter-light-breathe', $css);
        self::assertStringNotContainsString('bright_light_feet =', $js);
        self::assertStringNotContainsString('dim_light_feet =', $js);
    }

    public function test_doused_and_reduced_motion_emitters_are_static(): void
    {
        $css = file_get_contents($this->root('assets/css/tabletop.css'));

        self::assertIsString($css);
        self::assertStringContainsString('.gmrt-carried-light.is-environmental.is-doused .gmrt-keeper-light-marker', $css);
        self::assertStringContainsString('animation: none !important;', $css);
        self::assertStringContainsString('@media (prefers-reduced-motion: reduce)', $css);
        self::assertStringContainsString('.gmrt-light-emitter-particle', $css);
        self::assertStringContainsString('display: none;', $css);
    }

    public function test_phase_does_not_touch_authoritative_fog_or_light_projection(): void
    {
        $fog = file_get_contents($this->root('app/Tabletop/Fog/Services/FogOfWarProjector.php'));

        self::assertIsString($fog);
        self::assertStringNotContainsString('gmrt-emitter-', $fog);
        self::assertStringNotContainsString('is-dancing', $fog);
        self::assertStringContainsString("'light_attenuation' => array_map(", $fog);
        self::assertStringContainsString('SceneObjectLightOcclusionProjector', $fog);
    }
}
