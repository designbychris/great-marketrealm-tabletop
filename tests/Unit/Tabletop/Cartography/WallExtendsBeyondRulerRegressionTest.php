<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class WallExtendsBeyondRulerRegressionTest extends TestCase
{
    public function test_g5z50s_extends_only_the_fixed_tangent_with_bounded_original_image_evidence(): void
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertIsString($source);
        self::assertStringContainsString('IV.30.1G.5Z.50S — The Wall Extends Beyond the Ruler.', $source);
        self::assertStringContainsString('const barrierExtensionDistances=[1,2,3];', $source);
        self::assertStringContainsString('const barrierExtensionEvidenceThreshold=.34;', $source);
        self::assertStringContainsString('const barrierExtensionStrongThreshold=.62;', $source);
        self::assertStringContainsString('const barrierEndpointExtensionSamples=', $source);
        self::assertStringContainsString('const barrierConsecutiveExtension=', $source);
        self::assertStringContainsString('both-ends-extend-bypass-closed-review', $source);
        self::assertStringContainsString('one-end-extends-bypass-remains-review', $source);
        self::assertStringContainsString('G.5Z.50S wall extends beyond ruler', $source);
        self::assertStringContainsString('fixed-tangent-endpoint-extension-distances-1-2-3;moderate-original-image-ink-continuation-only;strong-ink-reported-separately;stop-at-first-unsupported-position;bounded-extended-corridor-connectivity-counterfactual;no-turn;no-route-shift;no-nearest-wall-search;no-recovery-replay;G.5Z.50-veto-unchanged', $source);
        self::assertStringContainsString('const maximumReviewSuggestions = 200;', $source);
        self::assertStringContainsString('const reconstructedSurfaceReviewCeiling = maximumReviewSuggestions;', $source);
        self::assertStringContainsString('const maximumPathVertices = 256;', $source);
    }

    public function test_g5z50s_publishes_extension_evidence_without_changing_the_production_barrier(): void
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertIsString($source);
        self::assertStringContainsString('illustratedPropagationBarrierRouteExtensionRuns,', $source);
        self::assertStringContainsString('illustratedPropagationBarrierRouteExtensionInterfaces,', $source);
        self::assertStringContainsString('illustratedPropagationBarrierRouteExtensionRoles,', $source);
        self::assertStringContainsString('illustratedPropagationBarrierRouteExtensionReviews,', $source);
        self::assertStringContainsString('bounded-extended-corridor-connectivity-counterfactual', $source);
        self::assertStringContainsString('no-turn;no-route-shift;no-nearest-wall-search;no-recovery-replay;G.5Z.50-veto-unchanged', $source);

        $roadmap = file_get_contents(dirname(__DIR__, 4) . '/ROADMAP.md');
        self::assertIsString($roadmap);
        self::assertStringContainsString('### IV.30.1G.5Z.50S — The Wall Extends Beyond the Ruler', $roadmap);
        self::assertStringContainsString('G.5Z.50 remains the active production barrier', $roadmap);
    }
}
