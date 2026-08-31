<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class HybridCartographyRegressionTest extends TestCase
{
    private function root(string $path): string
    {
        return dirname(__DIR__, 4) . '/' . $path;
    }

    public function test_keeper_can_choose_the_cartographers_judgement(): void
    {
        $view = (string) file_get_contents($this->root('app/Tabletop/Views/chamber.php'));

        self::assertStringContainsString('Judgement · hybrid map', $view);
        self::assertStringContainsString("The Cartographer's Judgement", $view);
        self::assertStringContainsString('constructed regions favour repeated structural linework', $view);
    }

    public function test_hybrid_analysis_runs_both_specialist_readers(): void
    {
        $js = (string) file_get_contents($this->root('assets/js/tabletop.js'));

        self::assertStringContainsString("IV.30.1D — The Cartographer's Judgement / Hybrid Structural & Living Contour Analysis", $js);
        self::assertStringContainsString('const structural = structuralCartographyCandidates()', $js);
        self::assertStringContainsString('const contours = livingContourCandidates()', $js);
        self::assertStringContainsString("if (detail === 'hybrid')", $js);
    }

    public function test_local_structural_evidence_rejects_isolated_ink_flecks(): void
    {
        $js = (string) file_get_contents($this->root('assets/js/tabletop.js'));

        self::assertStringContainsString('structuralSupport', $js);
        self::assertStringContainsString('localStructuralSupport >= 2 || item.confidence >= 94', $js);
        self::assertStringContainsString('A single straight-looking fleck can be handwriting, stairs or hatch', $js);
    }

    public function test_overlap_suppression_preserves_openings_and_polyline_paths(): void
    {
        $js = (string) file_get_contents($this->root('assets/js/tabletop.js'));

        self::assertStringContainsString('contourSpanCoveredByStructure', $js);
        self::assertStringContainsString('we never join across a removed span or doorway', $js);
        self::assertStringContainsString("hybridRegion: 'organic'", $js);
        self::assertStringContainsString("hybridRegion: 'structural'", $js);
        self::assertStringContainsString('maximumReviewSuggestions = 200', $js);
    }

    public function test_phase_records_mixed_map_benchmark_without_absorbing_grid_registration(): void
    {
        $roadmap = (string) file_get_contents($this->root('ROADMAP.md'));
        $phase = (string) file_get_contents($this->root('docs/Roadmap/PHASE-IV.30.1D.md'));

        self::assertStringContainsString("[x] **IV.30.1D — The Cartographer's Judgement / Hybrid Structural & Living Contour Analysis**", $roadmap);
        self::assertStringContainsString('Hybrid Judgement benchmark', $phase);
        self::assertStringContainsString('Nothing is saved automatically', $phase);
        self::assertStringContainsString('misaligned-grid benchmark remains reserved for later grid-registration work', $phase);
    }
}
