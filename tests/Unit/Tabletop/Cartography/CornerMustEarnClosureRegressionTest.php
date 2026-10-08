<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class CornerMustEarnClosureRegressionTest extends TestCase
{
    private string $source;

    protected function setUp(): void
    {
        parent::setUp();
        $this->source = (string) file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
    }

    public function test_phase_50v_requires_the_complete_b6_evidence_chain(): void
    {
        self::assertStringContainsString('IV.30.1G.5Z.50V — The Corner Must Earn Its Closure.', $this->source);
        self::assertStringContainsString("['candidate-role',closure.role==='candidate-floor-open']", $this->source);
        self::assertStringContainsString("['persistent-floor-to-open',beyond?.classification==='persistent-playable-floor-to-open-paper']", $this->source);
        self::assertStringContainsString("['coherent-route',shape?.classification==='coherent-bounded-route-review']", $this->source);
        self::assertStringContainsString("['residual-corner-gap',corner?.classification==='residual-bypass-corner-gap-review']", $this->source);
        self::assertStringContainsString("['corner-closure-eliminates',closure.classification==='corner-closure-eliminates-residual-bypass-review']", $this->source);
        self::assertStringContainsString('const exactSignature=failed.length===0;', $this->source);
    }

    public function test_phase_50v_keeps_floor_both_controls_and_reports_control_failures(): void
    {
        self::assertStringContainsString("const controlFailure=closure.role==='control-floor-both'&&closure.classification==='corner-closure-eliminates-residual-bypass-review';", $this->source);
        self::assertStringContainsString("'corner-closure-control-failure-review'", $this->source);
        self::assertStringContainsString("'earned-corner-closure-candidate-review'", $this->source);
        self::assertStringContainsString("'evidence-signature-mismatch-control-review'", $this->source);
        self::assertStringContainsString('illustratedPropagationBarrierCornerEarnedControlFailures', $this->source);
    }

    public function test_phase_50v_is_diagnostic_only_and_does_not_generalise_a_corner_rule(): void
    {
        self::assertStringContainsString('complete-B6-evidence-signature-generalisation-gate', $this->source);
        self::assertStringContainsString('existing-veto-population-through-full-route-survivors', $this->source);
        self::assertStringContainsString('exact-demonstrated-corner-only;floor-both-controls-retained;no-generic-corner-rule;no-new-wall-geometry;no-production-corner-closure', $this->source);
        self::assertStringContainsString('no-second-flood;no-global-flood;no-further-extension;no-turn;no-route-shift;no-nearest-wall-search;no-recovery-replay;G.5Z.50-veto-unchanged', $this->source);
    }

    public function test_phase_50v_is_published_in_the_evidence_audit(): void
    {
        self::assertStringContainsString('illustratedPropagationBarrierCornerEarnedPopulation,', $this->source);
        self::assertStringContainsString('illustratedPropagationBarrierCornerEarnedSurvivors,', $this->source);
        self::assertStringContainsString('illustratedPropagationBarrierCornerEarnedCandidates,', $this->source);
        self::assertStringContainsString('illustratedPropagationBarrierCornerEarnedEliminated,', $this->source);
        self::assertStringContainsString('illustratedPropagationBarrierCornerEarnedRemains,', $this->source);
        self::assertStringContainsString('illustratedPropagationBarrierCornerEarnedControlFailures,', $this->source);
        self::assertStringContainsString('G.5Z.50V corner must earn its closure', $this->source);
    }
}
