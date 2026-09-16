<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class RecoveredSurfacePropagationMultiWaveIllustratedFloodingRegressionTest extends TestCase
{
    public function test_recovered_surface_propagates_in_bounded_waves_without_weakening_vetoes(): void
    {
        $root = dirname(__DIR__, 4);
        $script = (string) file_get_contents($root . '/assets/js/tabletop.js');
        $roadmap = (string) file_get_contents($root . '/ROADMAP.md');

        self::assertStringContainsString('IV.30.1G.5Y — Recovered Surface Propagation & Multi-Wave Illustrated Flooding', $script);
        self::assertStringContainsString('const maximumPasses = Math.max(10, Math.round(contourSubdivisions * 4.5));', $script);
        self::assertStringContainsString('const quietIllustratedFloor = ink < .08 && adjacentPlayable >= 2 && local.playable >= 2;', $script);
        self::assertStringContainsString('if (!inkIsPlausibleDecoration && !quietIllustratedFloor)', $script);
        self::assertStringContainsString('if (exteriorLike) { illustratedFrontierExteriorRejects+=1; continue; }', $script);
        self::assertStringContainsString('illustratedPropagationWaves += 1;', $script);
        self::assertStringContainsString('if (pass > 0) illustratedPropagatedAdmissions+=1;', $script);
        self::assertStringContainsString('propagation waves', $script);
        self::assertStringContainsString('quiet-floor admissions', $script);
        self::assertStringContainsString('Phase IV.30.1G.5Y — Recovered Surface Propagation & Multi-Wave Illustrated Flooding ✅', $roadmap);
    }
}
