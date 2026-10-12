<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class CornerInkHoldingRegressionTest extends TestCase
{
    public function test_ink_holding_reuses_rightful_candidates_without_mutation(): void
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertIsString($source);
        self::assertStringContainsString('IV.30.1G.5Z.50Z.20 — The Ink Must Hold Its Line', $source);
        self::assertStringContainsString('for(const detail of rightful.details)', $source);
        self::assertStringContainsString("[...left].reverse().findIndex(v=>v!=='1')", $source);
        self::assertStringNotContainsString('left.reverse().findIndex', $source);
        self::assertStringContainsString("[...right].findIndex(v=>v!=='1')", $source);
        self::assertStringNotContainsString('right.findIndex(', $source);
        self::assertStringContainsString('line-holding-interrupted-direction-review', $source);
        self::assertStringContainsString('line-holding-ornament-competition-review', $source);
        self::assertStringContainsString('line-holding-window-incomplete-review', $source);
        self::assertStringContainsString('illustratedPropagationBarrierCornerInkHoldingReviews,', $source);
        self::assertStringContainsString('illustratedPropagationBarrierCornerInkHoldingSamples,', $source);
        self::assertStringContainsString('no-pixel-joining;no-snapping;no-wall-admission;no-recovery-replay', $source);
    }
}
