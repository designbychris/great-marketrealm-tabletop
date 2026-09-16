<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class RecoveredSurfaceThresholdInitializationOrderRegressionTest extends TestCase
{
    public function test_threshold_classifier_is_initialized_before_recovered_surface_completion_uses_it(): void
    {
        $root = dirname(__DIR__, 4);
        $script = (string) file_get_contents($root . '/assets/js/tabletop.js');
        $roadmap = (string) file_get_contents($root . '/ROADMAP.md');

        $initialization = strpos($script, 'const contourThresholdMatch = (a, b) => {');
        $completion = strpos($script, 'IV.30.1G.5Z — Recovered Surface Boundary Completion & Perimeter Continuity');
        $use = strpos($script, 'if (contourThresholdMatch(a,b)) {', $completion === false ? 0 : $completion);

        self::assertNotFalse($initialization);
        self::assertNotFalse($completion);
        self::assertNotFalse($use);
        self::assertLessThan($completion, $initialization);
        self::assertLessThan($use, $initialization);
        self::assertStringContainsString('IV.30.1G.5Z.1A — Threshold Classifier Initialization Order Correction', $script);
        self::assertStringContainsString('Phase IV.30.1G.5Z.1A — Threshold Classifier Initialization Order Correction ✅', $roadmap);
    }
}
