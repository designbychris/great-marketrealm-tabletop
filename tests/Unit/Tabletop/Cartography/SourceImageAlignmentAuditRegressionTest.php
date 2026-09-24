<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class SourceImageAlignmentAuditRegressionTest extends TestCase
{
    public function test_g5z42_reports_source_pixel_and_mesh_alignment_without_promoting_walls(): void
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertIsString($source);
        self::assertStringContainsString('IV.30.1G.5Z.42 — Source Image Alignment Audit', $source);
        self::assertStringContainsString('const sourceImageAlignmentAudit = (() => {', $source);
        self::assertStringContainsString('sourceToCanvasX = canvas.width / image.naturalWidth', $source);
        self::assertStringContainsString('const pixel = sourcePixelAt(sx, sy), mesh = meshInkAt(sx, sy)', $source);
        self::assertStringContainsString('sourceImageAlignmentAudit: sourceImageAlignmentAudit,', $source);
        self::assertStringContainsString('dataset.cartographySourceImageAlignment', $source);
        self::assertStringContainsString('G.5Z.42 source image alignment unavailable', $source);
        self::assertStringContainsString('unrepresented-walls-not-enumerated', $source);
        self::assertStringContainsString('wallCertified: false, admittedEdges: 0, restoredRuns: 0', $source);
    }
}
