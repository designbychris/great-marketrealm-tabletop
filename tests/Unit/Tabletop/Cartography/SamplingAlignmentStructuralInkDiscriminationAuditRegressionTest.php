<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class SamplingAlignmentStructuralInkDiscriminationAuditRegressionTest extends TestCase
{
    public function test_g5z41_keeps_paired_normal_ink_diagnostic_only(): void
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertIsString($source);
        self::assertStringContainsString('IV.30.1G.5Z.41 — Sampling Alignment & Structural Ink Discrimination Audit', $source);
        self::assertStringContainsString('const samplingAlignmentAudit = (() => {', $source);
        self::assertStringContainsString('positiveOnly', $source);
        self::assertStringContainsString('negativeOnly', $source);
        self::assertStringContainsString('samplingAlignmentAudit: samplingAlignmentAudit,', $source);
        self::assertStringContainsString('dataset.cartographySamplingAlignment', $source);
        self::assertStringContainsString('unrepresented-walls-not-enumerated', $source);
        self::assertStringContainsString('wallCertified: false, admittedEdges: 0, restoredRuns: 0', $source);
    }
}
