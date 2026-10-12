<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class CornerRightfulArmsRegressionTest extends TestCase
{
    public function test_rightful_arms_require_two_sustained_spokes_and_remain_diagnostic(): void
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertIsString($source);
        self::assertStringContainsString("IV.30.1G.5Z.50Z.15 — The Corner's Rightful Arms", $source);
        self::assertStringContainsString('illustratedPropagationBarrierCornerRightfulArmsReviews,', $source);
        self::assertStringContainsString('illustratedPropagationBarrierCornerRightfulArmsSamples,', $source);
        self::assertStringContainsString('longitudinal>=2&&transverse>=2', $source);
        self::assertStringContainsString('same-junction-two-sustained-arms-hypothesis-review', $source);
        self::assertStringContainsString("structuralPartner:'unproven',structuralHandshake:'unproven'", $source);
        self::assertStringContainsString('diagnostic-only;fixed-nine-by-nine-same-component-sustained-orthogonal-spokes;no-snapping;no-wall-admission;no-recovery-replay', $source);
    }
}
