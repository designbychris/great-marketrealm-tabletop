<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class DungeonForgeDecorationRegressionTest extends TestCase
{
    private function source(string $path): string
    {
        return (string) file_get_contents(dirname(__DIR__, 4) . '/' . $path);
    }

    public function test_forge_adds_a_dedicated_deterministic_decoration_layer(): void
    {
        $js = $this->source('assets/js/tabletop.js');

        self::assertStringContainsString("class: 'gmrt-forge-decoration'", $js);
        self::assertStringContainsString('decorationChance', $js);
        self::assertStringContainsString('`${plan.seed}|${plan.theme}|${x}|${y}|${salt}`', $js);
        self::assertStringContainsString('Themes are deterministic surface treatments only', $js);
    }

    public function test_each_theme_has_distinct_procedural_marks(): void
    {
        $js = $this->source('assets/js/tabletop.js');

        foreach (['is-crack','is-drain','is-root','is-ice-crack','is-brick','is-mushroom-cap','is-spore'] as $mark) {
            self::assertStringContainsString($mark, $js);
        }
        self::assertStringContainsString('is-rock-face', $js);
    }

    public function test_theme_styles_remain_presentation_only(): void
    {
        $css = $this->source('assets/css/tabletop.css');

        self::assertStringContainsString('.gmrt-forge-decoration', $css);
        self::assertStringContainsString('pointer-events:none', $css);
        self::assertStringContainsString('data-forge-theme="frostreem-vault"', $css);
        self::assertStringContainsString('data-forge-theme="mushroom-grotto"', $css);
    }

    public function test_generated_surfaces_suppress_image_backing(): void
    {
        $css = $this->source('assets/css/tabletop.css');
        self::assertStringContainsString('.gmrt-board__viewport.is-generated-surface .gmrt-board__map { display:none !important; }', $css);
    }

    public function test_phase_is_documented_and_versioned(): void
    {
        $plugin = $this->source('great-marketrealm-tabletop.php');
        $roadmap = $this->source('ROADMAP.md');
        $phase = $this->source('docs/Roadmap/PHASE-IV.30.2A.1.md');

        self::assertStringContainsString('0.32.0-alpha.7', $plugin);
        self::assertStringContainsString('IV.30.2A.1 — Pippin Decorates the Place', $roadmap);
        self::assertStringContainsString('presentation-only', $phase);
    }
}
