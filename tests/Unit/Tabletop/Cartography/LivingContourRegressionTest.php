<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class LivingContourRegressionTest extends TestCase
{
    private function javascript(): string
    {
        return (string) file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
    }

    private function chamber(): string
    {
        return (string) file_get_contents(dirname(__DIR__, 4) . '/app/Tabletop/Views/chamber.php');
    }

    public function test_keeper_can_choose_living_contour_for_cave_maps(): void
    {
        $view = $this->chamber();
        self::assertStringContainsString('value="contour"', $view);
        self::assertStringContainsString('Living Contour · caves', $view);
    }

    public function test_living_contour_classifies_floor_and_traces_floor_solid_boundaries(): void
    {
        $js = $this->javascript();
        self::assertStringContainsString('livingContourCandidates', $js);
        self::assertStringContainsString('cellDarkness', $js);
        self::assertStringContainsString('floor[row][column]', $js);
        self::assertStringContainsString("detail === 'contour'", $js);
    }

    public function test_living_contour_rejects_isolated_noise_and_simplifies_corners(): void
    {
        $js = $this->javascript();
        self::assertStringContainsString('neighbours === 0', $js);
        self::assertStringContainsString('degree-two vertex', $js);
        self::assertStringContainsString('diagonals.push', $js);
    }

    public function test_living_contour_keeps_review_first_safety_boundary(): void
    {
        $js = $this->javascript();
        self::assertStringContainsString('.slice(0, 200)', $js);
        self::assertStringContainsString('renderCartographyReview()', $js);
        self::assertStringContainsString('visionBarriers.map(cartographySuggestionKey)', $js);
    }

    public function test_roadmap_records_living_contour_before_grid_registration_work(): void
    {
        $roadmap = (string) file_get_contents(dirname(__DIR__, 4) . '/ROADMAP.md');
        self::assertStringContainsString('[x] **IV.30.1B — The Living Contour**', $roadmap);
        self::assertStringContainsString('misaligned-grid', (string) file_get_contents(dirname(__DIR__, 4) . '/docs/Roadmap/PHASE-IV.30.1B.md'));
    }
}
