<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class WallHasCornerRegressionTest extends TestCase
{
    private string $source;

    protected function setUp(): void
    {
        parent::setUp();
        $this->source = (string) file_get_contents(dirname(__DIR__, 5) . '/assets/js/tabletop.js');
    }

    public function test_phase_50t_keeps_the_residual_bypass_audit_bounded_and_diagnostic_only(): void
    {
        self::assertStringContainsString('IV.30.1G.5Z.50T — The Wall Has a Corner.', $this->source);
        self::assertStringContainsString('const barrierCornerEscapeReviews=barrierRouteExtensionReviews.map', $this->source);
        self::assertStringContainsString("?barrierTopologyConnectivity(outside,room,bounds,blocked):{connected:false,visited:0,path:[]}", $this->source);
        self::assertStringContainsString("path:path.slice(0,32).map", $this->source);
        self::assertStringContainsString('same-three-cell-local-envelope', $this->source);
        self::assertStringContainsString('no-second-flood;no-global-flood;no-further-extension;no-turn;no-route-shift;no-nearest-wall-search;no-recovery-replay;G.5Z.50-veto-unchanged', $this->source);
    }

    public function test_phase_50t_classifies_the_exact_remaining_path_without_wall_search(): void
    {
        self::assertStringContainsString("classification='residual-bypass-corner-gap-review'", $this->source);
        self::assertStringContainsString("classification='residual-bypass-around-extended-tangent-end-review'", $this->source);
        self::assertStringContainsString("classification='residual-bypass-along-corridor-normal-edge-review'", $this->source);
        self::assertStringContainsString("classification='residual-bypass-through-other-ink-review'", $this->source);
        self::assertStringContainsString("classification='residual-contiguous-floor-around-structure-review'", $this->source);
        self::assertStringContainsString('corridorCells.filter((route)=>adjacent(cell,route)).length>=2', $this->source);
    }

    public function test_phase_50t_is_published_in_the_evidence_audit(): void
    {
        self::assertStringContainsString('illustratedPropagationBarrierCornerEscapeRuns,', $this->source);
        self::assertStringContainsString('illustratedPropagationBarrierCornerEscapeInterfaces,', $this->source);
        self::assertStringContainsString('illustratedPropagationBarrierCornerEscapeRoles,', $this->source);
        self::assertStringContainsString('illustratedPropagationBarrierCornerEscapeReviews,', $this->source);
        self::assertStringContainsString('G.5Z.50T wall has a corner', $this->source);
        self::assertStringContainsString('exact-residual-shortest-path-after-G.5Z.50S-fixed-corridor', $this->source);
    }
}
