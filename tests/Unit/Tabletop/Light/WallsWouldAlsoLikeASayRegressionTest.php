<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Light;

use PHPUnit\Framework\TestCase;

final class WallsWouldAlsoLikeASayRegressionTest extends TestCase
{
    private function root(string $path): string
    {
        return dirname(__DIR__, 4) . '/' . ltrim($path, '/');
    }

    public function test_light_cells_are_derived_from_the_existing_barrier_aware_fog_mapper(): void
    {
        $fog = file_get_contents($this->root('app/Tabletop/Fog/Services/FogOfWarProjector.php'));
        $mapper = file_get_contents($this->root('app/Tabletop/Fog/Services/FogCellMapper.php'));

        self::assertIsString($fog);
        self::assertIsString($mapper);
        self::assertStringContainsString(
            '$illuminated = $mapper->visibleAround($scene, $lightSource, $barriers, $lightRadius);',
            $fog
        );
        self::assertStringContainsString("'light_cells' => \$lightCells", $fog);
        self::assertStringContainsString('(new SightLineResolver())->canSee(', $mapper);
        self::assertStringContainsString('$barriers', $mapper);
    }

    public function test_keeper_bypass_still_uses_wall_resolved_illumination_not_a_visual_circle(): void
    {
        $fog = file_get_contents($this->root('app/Tabletop/Fog/Services/FogOfWarProjector.php'));

        self::assertIsString($fog);
        self::assertStringContainsString('$sharedVisible = $dungeonMaster', $fog);
        self::assertStringContainsString('? $illuminated', $fog);
        self::assertStringContainsString(': array_values(array_intersect($illuminated, $viewerLineOfSight));', $fog);
    }

    public function test_light_cells_preserve_bright_dim_and_scene_object_transmission(): void
    {
        $fog = file_get_contents($this->root('app/Tabletop/Fog/Services/FogOfWarProjector.php'));

        self::assertIsString($fog);
        self::assertStringContainsString('$brightRadius = max(0, (int) ceil($brightFeet / 5));', $fog);
        self::assertStringContainsString('$baseIntensity = $distance <= $brightRadius ? 1.0 : 0.55;', $fog);
        self::assertStringContainsString('$baseIntensity * $transmission', $fog);
        self::assertStringContainsString("'tone' => \$lightTone", $fog);
    }

    public function test_browser_paints_only_authoritative_light_cells(): void
    {
        $js = file_get_contents($this->root('assets/js/tabletop.js'));
        $css = file_get_contents($this->root('assets/css/tabletop.css'));

        self::assertIsString($js);
        self::assertIsString($css);
        self::assertStringContainsString('fogProjection.light_cells', $js);
        self::assertStringContainsString('gmrt-authoritative-light-cell', $js);
        self::assertStringContainsString('--gmrt-authoritative-light', $js);
        self::assertStringContainsString('.gmrt-authoritative-light-cell.is-warm', $css);
        self::assertStringContainsString('.gmrt-authoritative-light-cell.is-cool', $css);
    }

    public function test_environmental_source_halo_is_local_only(): void
    {
        $css = file_get_contents($this->root('assets/css/tabletop.css'));

        self::assertIsString($css);
        self::assertStringContainsString('.gmrt-carried-light.is-environmental {', $css);
        self::assertStringContainsString('width: 30px;', $css);
        self::assertStringContainsString('height: 30px;', $css);
        self::assertStringContainsString('The old environmental diameter remains available as projection metadata', $css);
    }

    public function test_phase_reuses_existing_vision_barriers_and_keeps_animated_emitters(): void
    {
        $fog = file_get_contents($this->root('app/Tabletop/Fog/Services/FogOfWarProjector.php'));
        $css = file_get_contents($this->root('assets/css/tabletop.css'));

        self::assertIsString($fog);
        self::assertIsString($css);
        self::assertStringNotContainsString('LightBarrierRepository', $fog);
        self::assertStringNotContainsString('gmrt_light_wall', $fog);
        self::assertStringContainsString('@keyframes gmrt-emitter-brazier-dance', $css);
        self::assertStringContainsString('.gmrt-light-attenuation-cell', $css);
    }
}
