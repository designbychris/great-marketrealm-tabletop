<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class CornerBrokenArmsRegressionTest extends TestCase
{
    public function test_broken_arms_are_bounded_and_diagnostic_only(): void
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertIsString($source);
        self::assertStringContainsString("IV.30.1G.5Z.50Z.16 — The Corner's Broken Arms", $source);
        self::assertStringContainsString('illustratedPropagationBarrierCornerBrokenArmsReviews,', $source);
        self::assertStringContainsString('illustratedPropagationBarrierCornerBrokenArmsSamples,', $source);
        self::assertStringContainsString('rows[nextI][nextJ]===true&&k<4', $source);
        self::assertStringContainsString('single-gap-orthogonal-ink-hypothesis-review', $source);
        self::assertStringContainsString("structuralPartner:'unproven',structuralHandshake:'unproven'", $source);
        self::assertStringContainsString('no-pixel-joining;no-snapping;no-wall-admission;no-recovery-replay', $source);
    }
}
