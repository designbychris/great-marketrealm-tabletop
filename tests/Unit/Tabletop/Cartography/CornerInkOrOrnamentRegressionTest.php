<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class CornerInkOrOrnamentRegressionTest extends TestCase
{
    public function test_bounded_diagnostic_is_published_without_wall_admission(): void
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertIsString($source);
        self::assertStringContainsString('IV.30.1G.5Z.50Z.11 — The Corner\'s Ink or Ornament', $source);
        self::assertStringContainsString('illustratedPropagationBarrierCornerInkOrOrnamentReviews,', $source);
        self::assertStringContainsString('illustratedPropagationBarrierCornerInkOrOrnamentSamples,', $source);
        self::assertStringContainsString('ornament-hypothesis-not-wall-certification;no-snapping;no-wall-admission;no-recovery-replay', $source);
    }
}
