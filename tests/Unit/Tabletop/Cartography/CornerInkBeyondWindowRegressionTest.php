<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class CornerInkBeyondWindowRegressionTest extends TestCase
{
    public function test_wider_original_image_window_is_diagnostic_only(): void
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertIsString($source);
        self::assertStringContainsString('IV.30.1G.5Z.50Z.21 — The Ink Beyond the Window', $source);
        self::assertStringContainsString('for(const detail of holding.details)', $source);
        self::assertStringContainsString('const gap=gaps.get(detail.gap)', $source);
        self::assertStringContainsString('[-8,-7,-6,-5,-4].map(k=>sample(k))', $source);
        self::assertStringContainsString('[4,5,6,7,8].map(k=>sample(k))', $source);
        self::assertStringContainsString('beyond-window-parallel-ornament-competes-review', $source);
        self::assertStringContainsString('beyond-window-sampling-incomplete-review', $source);
        self::assertStringContainsString('illustratedPropagationBarrierCornerBeyondWindowReviews,', $source);
        self::assertStringContainsString('illustratedPropagationBarrierCornerBeyondWindowSamples,', $source);
        self::assertStringContainsString('no-axis-shift;no-pixel-joining;no-snapping;no-wall-admission;no-recovery-replay', $source);
    }
}
