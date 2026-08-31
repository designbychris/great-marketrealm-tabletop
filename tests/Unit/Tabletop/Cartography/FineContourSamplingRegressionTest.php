<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Cartography;

use GreatMarketrealmTabletop\Tabletop\Vision\Models\VisionBarrier;
use PHPUnit\Framework\TestCase;

final class FineContourSamplingRegressionTest extends TestCase
{
    private function root(string $path): string
    {
        return dirname(__DIR__, 4) . '/' . $path;
    }

    public function test_living_contour_uses_an_analysis_mesh_independent_of_gameplay_grid(): void
    {
        $js = (string) file_get_contents($this->root('assets/js/tabletop.js'));
        self::assertStringContainsString('maximumAnalysisCells = 32000', $js);
        self::assertStringContainsString('contourSubdivisions = 6', $js);
        self::assertStringContainsString('contourColumns = columns * contourSubdivisions', $js);
        self::assertStringContainsString('contourStep = 1 / contourSubdivisions', $js);
    }

    public function test_fine_mesh_is_adaptive_and_keeps_browser_work_bounded(): void
    {
        $js = (string) file_get_contents($this->root('assets/js/tabletop.js'));
        self::assertStringContainsString('contourSubdivisions > 2', $js);
        self::assertStringContainsString('> maximumAnalysisCells', $js);
        self::assertStringContainsString('contourSubdivisions -= 1', $js);
    }

    public function test_fine_contours_can_use_fractional_gameplay_grid_endpoints(): void
    {
        $barrier = new VisionBarrier('fine-wall', 'scene', VisionBarrier::WALL, 1.25, 2.5, 1.75, 3.0);
        self::assertSame(1.25, $barrier->x1());
        self::assertSame(2.5, $barrier->y1());
        self::assertSame(1.75, $barrier->x2());
        self::assertSame(3.0, $barrier->y2());
    }

    public function test_fine_contour_keeps_noise_cleanup_and_review_first_boundary(): void
    {
        $js = (string) file_get_contents($this->root('assets/js/tabletop.js'));
        self::assertStringContainsString('neighbours === 1', $js);
        self::assertStringContainsString('fineContour: true', $js);
        self::assertStringContainsString('.slice(0, 200)', $js);
        self::assertStringContainsString('renderCartographyReview()', $js);
    }

    public function test_roadmap_records_fine_sampling_before_grid_registration_work(): void
    {
        $roadmap = (string) file_get_contents($this->root('ROADMAP.md'));
        $phase = (string) file_get_contents($this->root('docs/Roadmap/PHASE-IV.30.1B.1.md'));
        self::assertStringContainsString('[x] **IV.30.1B.1 — Fine Contour Sampling**', $roadmap);
        self::assertStringContainsString('gameplay grid', $phase);
        self::assertStringContainsString('misaligned-grid', $phase);
    }
}
