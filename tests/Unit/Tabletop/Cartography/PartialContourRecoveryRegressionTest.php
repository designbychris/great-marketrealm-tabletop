<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class PartialContourRecoveryRegressionTest extends TestCase
{
    private function root(string $relative): string
    {
        return dirname(__DIR__, 4) . '/' . ltrim($relative, '/');
    }

    public function test_living_contour_keeps_safe_partial_chains_instead_of_failing_the_whole_draft(): void
    {
        $script = (string) file_get_contents($this->root('assets/js/tabletop.js'));

        self::assertStringContainsString('IV.30.1G.4 — Partial Contour Recovery', $script);
        self::assertStringContainsString('const recoverableChains = contourChains', $script);
        self::assertStringContainsString('partial: !entry.closed', $script);
        self::assertStringContainsString('const budgetedChains = recoverableChains.slice(0, maximumReviewSuggestions)', $script);
        self::assertStringNotContainsString('if (meaningfulChains.length > maximumReviewSuggestions) return []', $script);
    }

    public function test_open_contours_preserve_unresolved_ends_and_never_bridge_them_automatically(): void
    {
        $script = (string) file_get_contents($this->root('assets/js/tabletop.js'));

        self::assertStringContainsString("partialContourRecovery: entry.partial ? 'certified-open-chain' : 'closed-chain'", $script);
        self::assertStringContainsString('unresolvedBoundaryEnds: entry.partial ? [points[0], points[points.length - 1]] : []', $script);
        self::assertStringContainsString("'unresolved-ends-preserved'", $script);
        self::assertStringContainsString("evidenceModel: entry.partial ? 'living-contour-partial-v5'", $script);
    }

    public function test_hybrid_judgement_accepts_partial_organic_paths_without_upgrading_their_confidence(): void
    {
        $script = (string) file_get_contents($this->root('assets/js/tabletop.js'));

        self::assertStringContainsString('confidence: item.partialContour', $script);
        self::assertStringContainsString('partialContour: Boolean(item.partialContour)', $script);
        self::assertStringContainsString('unresolvedBoundaryEnds: Array.isArray(item.unresolvedBoundaryEnds)', $script);
        self::assertStringContainsString("hybridJudgement: true, hybridRegion: 'organic'", $script);
    }

    public function test_keeper_review_marks_partial_paths_and_phase_is_documented(): void
    {
        $script = (string) file_get_contents($this->root('assets/js/tabletop.js'));
        $roadmap = (string) file_get_contents($this->root('ROADMAP.md'));
        $phase = (string) file_get_contents($this->root('docs/Roadmap/PHASE-IV.30.1G.4.md'));

        self::assertStringContainsString("suggestion.partialContour ? 'Partial living wall path' : 'Living wall path'", $script);
        self::assertStringContainsString('unresolved ends kept open', $script);
        self::assertStringContainsString('[x] **IV.30.1G.4 — Partial Contour Recovery**', $roadmap);
        self::assertStringContainsString('Missing evidence is not permission to invent a wall.', $phase);
    }
}
