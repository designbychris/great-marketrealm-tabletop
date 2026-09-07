<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\SceneObjects;

use PHPUnit\Framework\TestCase;

final class LightFindsAnotherWayBrowserCorrectiveRegressionTest extends TestCase
{
    private function root(string $path): string
    {
        return dirname(__DIR__, 4) . '/' . ltrim($path, '/');
    }

    public function test_browser_accepts_authoritative_column_row_attenuation_keys(): void
    {
        $js = file_get_contents($this->root('assets/js/tabletop.js'));

        self::assertIsString($js);
        self::assertStringContainsString(
            'const match = /^(-?\d+):(-?\d+)$/.exec(String(key));',
            $js
        );
        self::assertStringNotContainsString(
            'const match = /^(-?\\\\d+):(-?\\\\d+)$/.exec(String(key));',
            $js
        );
        self::assertStringContainsString('gmrt-light-attenuation-cell', $js);
    }

    public function test_environmental_light_marker_visibility_uses_viewer_los_not_its_own_light_contribution(): void
    {
        $fog = file_get_contents($this->root('app/Tabletop/Fog/Services/FogOfWarProjector.php'));

        self::assertIsString($fog);
        self::assertStringContainsString(
            '|| in_array($sourceKey, $viewerLineOfSight, true)',
            $fog
        );
        self::assertStringContainsString(
            '|| in_array($sourceKey, $visible, true)',
            $fog
        );
    }

    public function test_environmental_presentation_metadata_survives_projection(): void
    {
        $fog = file_get_contents($this->root('app/Tabletop/Fog/Services/FogOfWarProjector.php'));

        self::assertIsString($fog);
        self::assertStringContainsString('$environmentalKind = $lightSource->kind();', $fog);
        self::assertStringContainsString('$environmentalLabel = $lightSource->label();', $fog);
        self::assertStringContainsString('$environmentalLit = $lightSource->lit();', $fog);
        self::assertStringContainsString("'environmental_kind' => \$environmentalKind", $fog);
        self::assertStringContainsString("'label' => \$environmentalLabel", $fog);
        self::assertStringContainsString("'lit' => \$environmentalLit", $fog);
    }
}
