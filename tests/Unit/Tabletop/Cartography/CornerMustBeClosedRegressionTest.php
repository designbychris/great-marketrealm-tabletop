<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class CornerMustBeClosedRegressionTest extends TestCase
{
    private string $source;

    protected function setUp(): void
    {
        parent::setUp();
        $this->source = (string) file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
    }

    public function test_phase_50u_closes_only_the_exact_corner_cells_demonstrated_by_50t(): void
    {
        self::assertStringContainsString('IV.30.1G.5Z.50U — The Corner Must Be Closed.', $this->source);
        self::assertStringContainsString('const barrierCornerClosureReviews=barrierCornerEscapeReviews.map', $this->source);
        self::assertStringContainsString("cornerAudit.classification==='residual-bypass-corner-gap-review'?cornerCells:[]", $this->source);
        self::assertStringContainsString('closedCornerCells.forEach((cell)=>blocked.add(barrierTopologyCellKey(cell.column,cell.row)))', $this->source);
        self::assertStringContainsString('closedCornerAdjacencies:closedCornerAdjacencies.map', $this->source);
    }

    public function test_phase_50u_is_a_bounded_causal_counterfactual_not_a_generic_corner_rule(): void
    {
        self::assertStringContainsString("classification=counterfactual.connected?'corner-closure-bypass-remains-review':'corner-closure-eliminates-residual-bypass-review'", $this->source);
        self::assertStringContainsString("classification='non-corner-residual-bypass-control-review'", $this->source);
        self::assertStringContainsString('exact-G.5Z.50T-demonstrated-corner-cell-closure-counterfactual', $this->source);
        self::assertStringContainsString('non-corner-controls-unchanged;same-three-cell-local-envelope;no-generic-corner-rule;no-new-wall-geometry', $this->source);
        self::assertStringContainsString('no-second-flood;no-global-flood;no-further-extension;no-turn;no-route-shift;no-nearest-wall-search;no-recovery-replay;G.5Z.50-veto-unchanged', $this->source);
    }

    public function test_phase_50u_is_published_in_the_evidence_audit(): void
    {
        self::assertStringContainsString('illustratedPropagationBarrierCornerClosureRuns,', $this->source);
        self::assertStringContainsString('illustratedPropagationBarrierCornerClosureInterfaces,', $this->source);
        self::assertStringContainsString('illustratedPropagationBarrierCornerClosureRoles,', $this->source);
        self::assertStringContainsString('illustratedPropagationBarrierCornerClosureReviews,', $this->source);
        self::assertStringContainsString('G.5Z.50U corner must be closed', $this->source);
    }
}
