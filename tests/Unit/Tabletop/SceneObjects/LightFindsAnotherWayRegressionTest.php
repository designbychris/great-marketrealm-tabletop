<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\SceneObjects;

use PHPUnit\Framework\TestCase;

final class LightFindsAnotherWayRegressionTest extends TestCase
{
    private function root(string $path): string
    {
        return dirname(__DIR__, 4) . '/' . ltrim($path, '/');
    }

    public function test_existing_fog_projector_consumes_scene_object_occlusion(): void
    {
        $fog = file_get_contents($this->root('app/Tabletop/Fog/Services/FogOfWarProjector.php'));

        self::assertIsString($fog);
        self::assertStringContainsString('SceneObjectLightOcclusionProjector', $fog);
        self::assertStringContainsString('$sceneObjectLight->occlusionBetween(', $fog);
        self::assertStringContainsString('$this->normalisedCellCentre($scene, (string) $cellKey)', $fog);
        self::assertStringContainsString("'light_attenuation' => array_map(", $fog);
        self::assertStringContainsString('if ($transmission > 1.0e-6)', $fog);
    }

    public function test_partial_occlusion_dims_but_complete_occlusion_removes_that_light_contribution(): void
    {
        $fog = file_get_contents($this->root('app/Tabletop/Fog/Services/FogOfWarProjector.php'));

        self::assertIsString($fog);
        self::assertStringContainsString('$transmission = max(0.0, min(1.0, 1.0 - $occlusion));', $fog);
        self::assertStringContainsString('$survivingVisible[] = (string) $cellKey;', $fog);
        self::assertStringContainsString('$visible = array_merge($visible, $survivingVisible);', $fog);
        self::assertStringNotContainsString('$visible = array_merge($visible, $sharedVisible);', $fog);
    }

    public function test_multiple_lights_keep_the_strongest_surviving_path(): void
    {
        $fog = file_get_contents($this->root('app/Tabletop/Fog/Services/FogOfWarProjector.php'));

        self::assertIsString($fog);
        self::assertStringContainsString('$lightTransmission[(string) $cellKey] = max(', $fog);
        self::assertStringContainsString('(float) ($lightTransmission[(string) $cellKey] ?? 0.0)', $fog);
        self::assertStringContainsString('$transmission', $fog);
    }

    public function test_all_existing_light_kinds_still_share_the_same_projection_pipeline(): void
    {
        $fog = file_get_contents($this->root('app/Tabletop/Fog/Services/FogOfWarProjector.php'));

        self::assertIsString($fog);
        self::assertStringContainsString('instanceof EnvironmentalLight', $fog);
        self::assertStringContainsString('instanceof DroppedLight', $fog);
        self::assertStringContainsString("(\$lightSource['magical_light'] ?? null) instanceof MagicalLight", $fog);
        self::assertStringContainsString("\$sourceKind = 'carried';", $fog);
        self::assertStringContainsString('$mapper->visibleAround($scene, $lightSource, $barriers, $lightRadius)', $fog);
    }

    public function test_browser_renders_attenuation_without_becoming_a_second_authority(): void
    {
        $js = file_get_contents($this->root('assets/js/tabletop.js'));
        $css = file_get_contents($this->root('assets/css/tabletop.css'));

        self::assertIsString($js);
        self::assertIsString($css);
        self::assertStringContainsString('fogProjection.light_attenuation', $js);
        self::assertStringContainsString('gmrt-light-attenuation-cell', $js);
        self::assertStringContainsString('--gmrt-light-attenuation', $js);
        self::assertStringContainsString('.gmrt-light-attenuation-cell', $css);
        self::assertStringContainsString('pointer-events: none', $css);
    }

    public function test_phase_does_not_smuggle_in_animated_emitters_or_new_light_persistence(): void
    {
        $fog = file_get_contents($this->root('app/Tabletop/Fog/Services/FogOfWarProjector.php'));
        $js = file_get_contents($this->root('assets/js/tabletop.js'));

        self::assertIsString($fog);
        self::assertIsString($js);
        self::assertStringNotContainsString('gmrt_animated_light', $fog);
        self::assertStringNotContainsString('requestAnimationFrame', $fog);
        self::assertStringNotContainsString('light_sprite_sheet', $js);
    }
}
