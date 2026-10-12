<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class PippinStudiesInkRegressionTest extends TestCase
{
    public function test_ink_study_uses_original_illustration_and_preserves_geometry(): void
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertIsString($source);
        self::assertStringContainsString('IV.30.2C.3 — Pippin Studies the Ink', $source);
        self::assertStringContainsString('pippinsPencil.candidates.filter', $source);
        self::assertStringContainsString('luminance(sx, sy) <= darkThreshold', $source);
        self::assertStringContainsString('shared-endpoint-competition', $source);
        self::assertStringContainsString('hatching-or-parallel-ink-competes-review', $source);
        self::assertStringContainsString('proposedGeometry:0,changedGeometry:0', $source);
        self::assertStringContainsString('pippinInkStudy,', $source);
        self::assertStringContainsString('IV.30.2C.3 Pippin studies the ink', $source);
    }
}
