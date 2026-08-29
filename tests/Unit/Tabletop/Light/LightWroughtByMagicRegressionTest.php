<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Light;

use PHPUnit\Framework\TestCase;

final class LightWroughtByMagicRegressionTest extends TestCase
{
    private function root(string $path): string { return dirname(__DIR__, 4) . '/' . $path; }

    public function test_magical_light_is_companion_certified_and_server_authoritative(): void
    {
        $controller = file_get_contents($this->root('app/Tabletop/Http/MagicalLightAjaxController.php'));
        self::assertStringContainsString("spell_id", $controller);
        self::assertStringNotContainsString("bright_feet'] ?? \\$_POST", $controller);
        self::assertStringContainsString("\$spell['illumination']", $controller);
        self::assertStringContainsString("\$illum['bright_feet']", $controller);
        self::assertStringContainsString("\$illum['dim_feet']", $controller);
        self::assertStringContainsString('Place your Companion Character in this Chamber', $controller);
    }

    public function test_shelfshine_uses_shared_los_and_barriers_in_the_living_veil(): void
    {
        $fog = file_get_contents($this->root('app/Tabletop/Fog/Services/FogOfWarProjector.php'));
        self::assertStringContainsString("\$sourceKind = 'magical'", $fog);
        self::assertStringContainsString('$brightFeet = $magicalLight->brightFeet()', $fog);
        self::assertStringContainsString('$dimFeet = $magicalLight->dimFeet()', $fog);
        self::assertStringContainsString('visibleAround($scene, $lightSource, $barriers, $lightRadius)', $fog);
        self::assertStringContainsString('array_intersect($illuminated, $viewerLineOfSight)', $fog);
    }

    public function test_shelfshine_has_a_pixel_magic_presentation_with_reduced_motion(): void
    {
        $js = file_get_contents($this->root('assets/js/tabletop.js'));
        $css = file_get_contents($this->root('assets/css/tabletop.css'));
        self::assertStringContainsString("sourceKind === 'magical'", $js);
        self::assertStringContainsString('gmrt-shelfshine-spark', $js);
        self::assertStringContainsString('@keyframes gmrt-shelfshine-pulse', $css);
        self::assertStringContainsString('prefers-reduced-motion: reduce', $css);
    }
}
