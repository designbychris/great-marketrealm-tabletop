<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class AdaptiveContourReductionRegressionTest extends TestCase
{
    private function root(string $path): string
    {
        return dirname(__DIR__, 4) . '/' . $path;
    }

    public function test_cartographer_allocates_the_review_budget_across_complete_contours(): void
    {
        $js = (string) file_get_contents($this->root('assets/js/tabletop.js'));
        self::assertStringContainsString("The Cartographer's Economy / Adaptive Contour Reduction", $js);
        self::assertStringContainsString('maximumReviewSuggestions = 200', $js);
        self::assertStringContainsString('budgetedChains', $js);
        self::assertStringContainsString('remainingBudget', $js);
        self::assertStringContainsString('Math.sqrt(entry.length)', $js);
    }

    public function test_tiny_fine_mesh_loops_are_suppressed_before_budgeting(): void
    {
        $js = (string) file_get_contents($this->root('assets/js/tabletop.js'));
        self::assertStringContainsString('meaningfulChains', $js);
        self::assertStringContainsString('entry.length >= contourStep * 2.5', $js);
        self::assertStringContainsString('hatch/ink loops are suppressed', $js);
    }

    public function test_each_contour_is_simplified_to_its_own_target(): void
    {
        $js = (string) file_get_contents($this->root('assets/js/tabletop.js'));
        self::assertStringContainsString('simplifyChainToTarget', $js);
        self::assertStringContainsString('entry.target', $js);
        self::assertStringContainsString('for (let pass = 0; pass < 14; pass += 1)', $js);
        self::assertStringContainsString('adaptiveBudget: true', $js);
    }

    public function test_old_global_simplifier_is_only_a_defensive_fallback(): void
    {
        $js = (string) file_get_contents($this->root('assets/js/tabletop.js'));
        self::assertStringContainsString('Defensive compatibility fallback', $js);
        self::assertStringContainsString('simplificationTolerance *= 1.35', $js);
        self::assertStringContainsString('if (fallbackValues.length > maximumReviewSuggestions) return []', $js);
    }

    public function test_roadmap_records_adaptive_reduction_without_absorbing_grid_registration(): void
    {
        $roadmap = (string) file_get_contents($this->root('ROADMAP.md'));
        $phase = (string) file_get_contents($this->root('docs/Roadmap/PHASE-IV.30.1B.3.md'));
        self::assertStringContainsString("[x] **IV.30.1B.3 — The Cartographer's Economy / Adaptive Contour Reduction**", $roadmap);
        self::assertStringContainsString('Advanced — cave dungeon', $phase);
        self::assertStringContainsString('200-segment review budget', $phase);
        self::assertStringContainsString('misaligned-grid benchmark remains reserved for later grid-registration work', $phase);
    }
}
