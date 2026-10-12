<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class CornerBrokenStrokeWitnessRegressionTest extends TestCase
{
    public function test_broken_stroke_witness_is_bounded_and_diagnostic_only(): void
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertIsString($source);
        self::assertStringContainsString('IV.30.1G.5Z.50Z.22 — The Broken Stroke\'s Witness', $source);
        self::assertStringContainsString('for(const detail of beyond.details)', $source);
        self::assertStringContainsString('broken-stroke-parallel-hatching-competes-review', $source);
        self::assertStringContainsString('broken-stroke-directional-alignment-hypothesis-review', $source);
        self::assertStringContainsString('broken-stroke-fragmented-or-independent-review', $source);
        self::assertStringContainsString('illustratedPropagationBarrierCornerBrokenStrokeReviews,', $source);
        self::assertStringContainsString('illustratedPropagationBarrierCornerBrokenStrokeSamples,', $source);
        self::assertStringContainsString('no-pixel-joining;no-snapping;no-wall-admission;no-recovery-replay', $source);
    }
}
