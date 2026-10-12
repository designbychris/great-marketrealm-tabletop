<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class CornerRightfulInkRegressionTest extends TestCase
{
    public function test_rightful_ink_reuses_borrowed_ink_gaps_and_remains_diagnostic_only(): void
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertIsString($source);
        self::assertStringContainsString("IV.30.1G.5Z.50Z.19 — The Corner's Rightful Ink", $source);
        self::assertStringContainsString('illustratedPropagationBarrierCornerRightfulInkReviews,', $source);
        self::assertStringContainsString('illustratedPropagationBarrierCornerRightfulInkSamples,', $source);
        self::assertStringContainsString('unborrowed-axial-ink-hypothesis-review', $source);
        self::assertStringContainsString('rightful-ink-borrowed-by-ornament-review', $source);
        self::assertStringContainsString('rightful-ink-interrupted-axis-review', $source);
        self::assertStringContainsString('rightful-ink-window-incomplete-review', $source);
        self::assertStringContainsString('rightful.ornamentControls++;continue;', $source);
        self::assertStringContainsString("structuralPartner:'unproven',structuralHandshake:'unproven'", $source);
        self::assertStringContainsString('no-pixel-joining;no-snapping;no-wall-admission;no-recovery-replay', $source);
    }
}
