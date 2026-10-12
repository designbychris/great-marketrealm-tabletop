<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class CornerFalseBridgesRegressionTest extends TestCase
{
    public function test_false_bridge_evidence_is_bounded_and_diagnostic_only(): void
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertIsString($source);
        self::assertStringContainsString("IV.30.1G.5Z.50Z.17 — The Corner's False Bridges", $source);
        self::assertStringContainsString('illustratedPropagationBarrierCornerFalseBridgesReviews,', $source);
        self::assertStringContainsString('illustratedPropagationBarrierCornerFalseBridgesSamples,', $source);
        self::assertStringContainsString('parallel-hatch-competes-with-bridge-review', $source);
        self::assertStringContainsString('collinear-bracket-without-local-hatch-review', $source);
        self::assertStringContainsString('gaps.has(key)', $source);
        self::assertStringContainsString("structuralPartner:'unproven',structuralHandshake:'unproven'", $source);
        self::assertStringContainsString('no-pixel-joining;no-snapping;no-wall-admission;no-recovery-replay', $source);
    }
}
