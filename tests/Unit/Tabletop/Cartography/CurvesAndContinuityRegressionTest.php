<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class CurvesAndContinuityRegressionTest extends TestCase
{
    private function root(string $relative): string
    {
        return dirname(__DIR__, 4) . '/' . ltrim($relative, '/');
    }

    public function test_structural_tracing_adds_both_diagonal_passes(): void
    {
        $script = (string) file_get_contents($this->root('assets/js/tabletop.js'));
        self::assertStringContainsString('const diagonalDownStarts = []', $script);
        self::assertStringContainsString('traceDirectional(diagonalDownStarts, 1, 1, Math.SQRT1_2, -Math.SQRT1_2)', $script);
        self::assertStringContainsString('const diagonalUpStarts = []', $script);
        self::assertStringContainsString('traceDirectional(diagonalUpStarts, 1, -1, Math.SQRT1_2, Math.SQRT1_2)', $script);
    }

    public function test_continuity_repair_is_conservative_and_bounded_to_one_weak_sample(): void
    {
        $script = (string) file_get_contents($this->root('assets/js/tabletop.js'));
        self::assertStringContainsString('continuity: center >= .10', $script);
        self::assertStringContainsString('let gapBudget = 1', $script);
        self::assertStringContainsString('score.continuity && gapBudget > 0', $script);
        self::assertStringContainsString('gapBudget -= 1', $script);
    }

    public function test_curved_traces_are_simplified_after_free_position_analysis(): void
    {
        $script = (string) file_get_contents($this->root('assets/js/tabletop.js'));
        self::assertStringContainsString('const distance = Math.hypot(dx, dy)', $script);
        self::assertStringContainsString('const pieces = Math.max(1, Math.ceil(distance / gridCanvas))', $script);
        self::assertStringContainsString('vote(trace.x1 + dx * from', $script);
        self::assertStringContainsString('if (dx > 1 || dy > 1) return', $script);
    }

    public function test_keeper_copy_explains_diagonal_and_organic_structural_approximation(): void
    {
        $view = (string) file_get_contents($this->root('app/Tabletop/Views/chamber.php'));
        self::assertStringContainsString('including diagonals and curved/organic boundaries', $view);
        self::assertStringContainsString('short connected segments', $view);
    }

    public function test_phase_document_records_control_advanced_and_hostile_benchmarks(): void
    {
        $document = (string) file_get_contents($this->root('docs/Roadmap/PHASE-IV.30.1A.md'));
        self::assertStringContainsString('Control — regular dungeon', $document);
        self::assertStringContainsString('Advanced — cave dungeon', $document);
        self::assertStringContainsString('Hostile — misaligned-grid dungeon', $document);
        self::assertStringContainsString('Nothing is saved automatically', $document);
    }
}
