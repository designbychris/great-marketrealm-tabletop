<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class InkPreservationAuditRegressionTest extends TestCase
{
    public function test_g5z44_reproduces_mesh_sampler_without_promoting_pixel_ink_to_walls(): void
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertIsString($source);
        self::assertStringContainsString('IV.30.1G.5Z.44 — Ink Preservation Audit', $source);
        self::assertStringContainsString('const inkPreservationAudit = (() => {', $source);
        self::assertStringContainsString('const insetLeft = left + contourCellX * .14;', $source);
        self::assertStringContainsString('const stepX = Math.max(1, Math.round(contourCellX / 5));', $source);
        self::assertStringContainsString('samplerMatches', $source);
        self::assertStringContainsString('inkPreservationAudit: inkPreservationAudit,', $source);
        self::assertStringContainsString('dataset.cartographyInkPreservation', $source);
        self::assertStringContainsString('G.5Z.44 ink preservation unavailable', $source);
        self::assertStringContainsString('wallCertified: false, admittedEdges: 0, restoredRuns: 0', $source);
    }
}
