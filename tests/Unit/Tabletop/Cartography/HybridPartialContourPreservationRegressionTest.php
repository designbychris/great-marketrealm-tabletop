<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class HybridPartialContourPreservationRegressionTest extends TestCase
{
    private function root(string $relative): string
    {
        return dirname(__DIR__, 4) . '/' . ltrim($relative, '/');
    }

    public function test_hybrid_falls_back_when_connected_floor_contours_prove_nothing(): void
    {
        $script = (string) file_get_contents($this->root('assets/js/tabletop.js'));

        self::assertStringContainsString('IV.30.1G.4A — Hybrid Partial-Contour Preservation', $script);
        self::assertStringContainsString('const connectedContours = livingContourCandidates({ connectPlayableFloor: true });', $script);
        self::assertStringContainsString('const standaloneContours = livingContourCandidates({ thresholdCandidates });', $script);
        self::assertStringContainsString("const hybridContourSource = connectedContours.length > 0 ? 'certified-dual-living-readers' : 'standalone-fallback';", $script);
    }

    public function test_partial_contour_uncertainty_survives_hybrid_without_auto_bridging(): void
    {
        $script = (string) file_get_contents($this->root('assets/js/tabletop.js'));

        self::assertStringContainsString('hybridPartialPreservation: Boolean(item.partialContour)', $script);
        self::assertStringContainsString('unresolvedBoundaryEnds: Array.isArray(item.unresolvedBoundaryEnds)', $script);
        self::assertStringContainsString("'hybrid-partial-contour-v5a'", $script);
        self::assertStringNotContainsString('bridgeHybridPartialContour', $script);
    }

    public function test_review_budget_ranks_safe_organic_paths_instead_of_rejecting_the_whole_hybrid(): void
    {
        $script = (string) file_get_contents($this->root('assets/js/tabletop.js'));

        self::assertStringContainsString('const organicUsefulness = (item) => {', $script);
        self::assertStringContainsString('const retainedOrganic = organic.slice(0, maximumReviewSuggestions);', $script);
        self::assertStringNotContainsString('if (organic.length > maximumReviewSuggestions) return []', $script);
    }

    public function test_post_trim_empty_hybrid_gets_one_safe_standalone_contour_fallback_and_phase_is_documented(): void
    {
        $script = (string) file_get_contents($this->root('assets/js/tabletop.js'));
        $roadmap = (string) file_get_contents($this->root('ROADMAP.md'));
        $phase = (string) file_get_contents($this->root('docs/Roadmap/PHASE-IV.30.1G.4A.md'));

        self::assertStringContainsString("if (combined.length === 0 && hybridContourSource === 'certified-dual-living-readers')", $script);
        self::assertStringContainsString("hybridContourSource: 'standalone-post-trim-fallback'", $script);
        self::assertStringContainsString('[x] **IV.30.1G.4A — Hybrid Partial-Contour Preservation**', $roadmap);
        self::assertStringContainsString('Hybrid may combine what Pippin knows, but it must not erase what Pippin already proved.', $phase);
    }
}
