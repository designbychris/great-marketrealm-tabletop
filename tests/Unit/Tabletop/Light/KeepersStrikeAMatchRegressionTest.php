<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Light;

use PHPUnit\Framework\TestCase;

final class KeepersStrikeAMatchRegressionTest extends TestCase
{
    private function source(string $path): string
    {
        return (string) file_get_contents(dirname(__DIR__, 4) . '/' . $path);
    }

    public function test_all_keeper_light_presets_have_the_certified_radii(): void
    {
        $source = $this->source('app/Tabletop/Http/EnvironmentalLightAjaxController.php');

        self::assertStringContainsString("'torch' => ['label' => 'Torch', 'bright' => 20, 'dim' => 20]", $source);
        self::assertStringContainsString("'lantern' => ['label' => 'Lantern', 'bright' => 30, 'dim' => 30]", $source);
        self::assertStringContainsString("'brazier' => ['label' => 'Brazier', 'bright' => 60, 'dim' => 60]", $source);
        self::assertStringContainsString("'candle' => ['label' => 'Candle', 'bright' => 10, 'dim' => 10]", $source);
        self::assertStringContainsString("'magical' => ['label' => 'Magical Light', 'bright' => 40, 'dim' => 40]", $source);
    }

    public function test_new_keeper_lights_are_explicitly_placed_lit(): void
    {
        $source = $this->source('app/Tabletop/Http/EnvironmentalLightAjaxController.php');

        self::assertStringContainsString('new EnvironmentalLight($id,$tableId,$sceneId,$kind,$label,$x,$y,$bright,$dim,true)', $source);
        self::assertStringContainsString("placed and burning.", $source);
    }

    public function test_roster_exposes_lit_or_doused_state_and_the_inverse_action(): void
    {
        $source = $this->source('assets/js/tabletop.js');

        self::assertStringContainsString("state.textContent=isLit ? '● Lit' : '○ Doused'", $source);
        self::assertStringContainsString("douse.textContent=isLit ? 'Douse' : 'Light'", $source);
        self::assertStringContainsString("douse.dataset.keeperLightAction=isLit ? 'douse' : 'light'", $source);
        self::assertStringContainsString('`${brightFeet} ft radius`', $source);
    }

    public function test_keeper_uses_explicit_light_and_douse_actions(): void
    {
        $controller = $this->source('app/Tabletop/Http/EnvironmentalLightAjaxController.php');
        $js = $this->source('assets/js/tabletop.js');

        self::assertStringContainsString("in_array(\$action,['light','douse','toggle'],true)", $controller);
        self::assertStringContainsString("button.dataset.keeperLightAction || 'toggle'", $js);
    }

    public function test_environmental_glow_uses_projected_light_range_and_calibrated_grid(): void
    {
        $js = $this->source('assets/js/tabletop.js');
        $css = $this->source('assets/css/tabletop.css');

        self::assertStringContainsString('const radiusSquares = rangeFeet / 5', $js);
        self::assertStringContainsString("glow.style.setProperty('--gmrt-light-diameter'", $js);
        self::assertStringContainsString('width:var(--gmrt-light-diameter,18%)', $css);
    }

    public function test_phase_is_documented_and_versioned(): void
    {
        $plugin = $this->source('great-marketrealm-tabletop.php');
        $roadmap = $this->source('ROADMAP.md');
        $phase = $this->source('docs/Roadmap/PHASE-IV.31.1.md');

        self::assertStringContainsString('0.32.0-alpha.1', $plugin);
        self::assertStringContainsString("IV.31.1 — The Keeper Strikes a Match", $roadmap);
        self::assertStringContainsString('placed lit by default', $phase);
    }
}
