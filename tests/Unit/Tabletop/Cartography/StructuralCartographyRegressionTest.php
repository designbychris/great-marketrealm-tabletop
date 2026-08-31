<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class StructuralCartographyRegressionTest extends TestCase
{
    private function root(string $relative): string
    {
        return dirname(__DIR__, 4) . '/' . ltrim($relative, '/');
    }

    public function test_structural_mode_is_offered_for_inked_dungeon_maps(): void
    {
        $view = (string) file_get_contents($this->root('app/Tabletop/Views/chamber.php'));
        self::assertStringContainsString('<option value="structural">Structural tracing</option>', $view);
        self::assertStringContainsString('thick inked dungeon walls', $view);
    }

    public function test_structural_pass_builds_a_dark_pixel_mask_before_tracing(): void
    {
        $script = (string) file_get_contents($this->root('assets/js/tabletop.js'));
        self::assertStringContainsString('structuralCartographyCandidates', $script);
        self::assertStringContainsString('const darkThreshold = 92', $script);
        self::assertStringContainsString('const dark = new Uint8Array', $script);
    }

    public function test_structural_pass_suppresses_hatch_texture_by_comparing_across_the_trace(): void
    {
        $script = (string) file_get_contents($this->root('assets/js/tabletop.js'));
        self::assertStringContainsString('Math.min(above, below) <= .16', $script);
        self::assertStringContainsString('Math.min(left, right) <= .16', $script);
        self::assertStringContainsString('center >= Math.max(above, below) * 1.25', $script);
    }

    public function test_structural_traces_are_segmented_back_into_existing_grid_barriers(): void
    {
        $script = (string) file_get_contents($this->root('assets/js/tabletop.js'));
        self::assertStringContainsString('const pieces = Math.max(1, Math.ceil(distance / gridCanvas))', $script);
        self::assertStringContainsString('vote(trace.x1 + dx * from', $script);
        self::assertStringContainsString("type: 'wall'", $script);
    }

    public function test_structural_draft_remains_review_first_and_bounded(): void
    {
        $script = (string) file_get_contents($this->root('assets/js/tabletop.js'));
        self::assertStringContainsString("if (detail === 'structural')", $script);
        self::assertStringContainsString('.slice(0, 200)', $script);
        self::assertStringContainsString('renderCartographyReview()', $script);
        self::assertStringContainsString('.filter((item) => item.selected)', $script);
    }
}
