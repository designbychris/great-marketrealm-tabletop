<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Cartography;

use GreatMarketrealmTabletop\Tabletop\Vision\Models\VisionBarrier;
use GreatMarketrealmTabletop\Tabletop\Vision\Services\SightLineResolver;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class PolylineVisionBarrierRegressionTest extends TestCase
{
    private function root(string $path): string
    {
        return dirname(__DIR__, 4) . '/' . $path;
    }

    public function test_wall_path_persists_ordered_vertices_while_legacy_endpoints_remain_available(): void
    {
        $barrier = VisionBarrier::path('path', 'scene', [
            ['x' => 1.0, 'y' => 0.0],
            ['x' => 1.0, 'y' => 1.0],
            ['x' => 2.0, 'y' => 1.0],
        ]);

        self::assertTrue($barrier->isPath());
        self::assertSame(1.0, $barrier->x1());
        self::assertSame(0.0, $barrier->y1());
        self::assertSame(2.0, $barrier->x2());
        self::assertSame(1.0, $barrier->y2());
        self::assertCount(3, $barrier->toArray()['points']);
        self::assertCount(2, $barrier->segments());
    }

    public function test_line_of_sight_checks_every_span_inside_one_wall_path(): void
    {
        $barrier = VisionBarrier::path('path', 'scene', [
            ['x' => 1.0, 'y' => 0.0],
            ['x' => 1.0, 'y' => 2.0],
            ['x' => 3.0, 'y' => 2.0],
        ]);
        $resolver = new SightLineResolver();

        self::assertFalse($resolver->canSee(0, 0, 2, 0, [$barrier]));
        self::assertFalse($resolver->canSee(2, 1, 2, 2, [$barrier]));
    }

    public function test_doors_cannot_become_multi_vertex_paths(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new VisionBarrier('door', 'scene', VisionBarrier::DOOR, 0, 0, 1, 1, false, [
            ['x' => 0, 'y' => 0],
            ['x' => 1, 'y' => 0],
            ['x' => 1, 'y' => 1],
        ]);
    }

    public function test_living_contour_reviews_and_applies_complete_polyline_objects(): void
    {
        $js = (string) file_get_contents($this->root('assets/js/tabletop.js'));
        $manager = (string) file_get_contents($this->root('app/Tabletop/Vision/Services/VisionBarrierManager.php'));

        self::assertStringContainsString("IV.30.1C — The Cartographer's Linework / Polyline Vision Barriers", $js);
        self::assertStringContainsString('polyline: true, points', $js);
        self::assertStringContainsString("points.length > 2 ? 'polyline' : 'line'", $js);
        self::assertStringContainsString("return { type: 'wall', points: item.points }", $js);
        self::assertStringContainsString('MAX_PATH_POINTS = 256', $manager);
        self::assertStringContainsString('MAX_BATCH_POINTS = 6000', $manager);
    }

    public function test_phase_keeps_review_first_authority_and_grid_registration_separate(): void
    {
        $roadmap = (string) file_get_contents($this->root('ROADMAP.md'));
        $phase = (string) file_get_contents($this->root('docs/Roadmap/PHASE-IV.30.1C.md'));

        self::assertStringContainsString("[x] **IV.30.1C — The Cartographer's Linework / Polyline Vision Barriers**", $roadmap);
        self::assertStringContainsString('Nothing is saved automatically', $phase);
        self::assertStringContainsString('200 objects and 6,000 total vertices', $phase);
        self::assertStringContainsString('misaligned-grid benchmark remains reserved for later grid-registration work', $phase);
    }
}
