<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class CornerTrueJunctionRegressionTest extends TestCase
{
    public function test_true_junction_requires_same_component_and_remains_diagnostic(): void
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertIsString($source);
        self::assertStringContainsString("IV.30.1G.5Z.50Z.14 — The Corner's True Junction", $source);
        self::assertStringContainsString('illustratedPropagationBarrierCornerTrueJunctionReviews,', $source);
        self::assertStringContainsString('illustratedPropagationBarrierCornerTrueJunctionSamples,', $source);
        self::assertStringContainsString('near&&longitudinal&&transverse', $source);
        self::assertStringContainsString('same-component-cross-axis-junction-hypothesis-review', $source);
        self::assertStringContainsString("structuralPartner:'unproven',structuralHandshake:'unproven'", $source);
        self::assertStringContainsString('diagnostic-only;fixed-nine-by-nine-same-component-cross-axis-junction;no-snapping;no-wall-admission;no-recovery-replay', $source);
    }
}
