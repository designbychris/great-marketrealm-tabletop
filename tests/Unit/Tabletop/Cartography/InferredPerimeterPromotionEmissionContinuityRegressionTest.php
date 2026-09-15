<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class InferredPerimeterPromotionEmissionContinuityRegressionTest extends TestCase
{
    public function test_inferred_perimeter_edges_are_grouped_before_the_review_object_budget(): void
    {
        $root = dirname(__DIR__, 4);
        $script = (string) file_get_contents($root . '/assets/js/tabletop.js');
        $roadmap = (string) file_get_contents($root . '/ROADMAP.md');

        self::assertStringContainsString('promoteInferredPerimeterChains', $script);
        self::assertStringContainsString("inferredPerimeterPromotion: true", $script);
        self::assertStringContainsString("emissionContinuity: 'connected-perimeter-chain'", $script);
        self::assertStringContainsString("evidenceModel: propagated ? 'living-contour-inferred-perimeter-promotion-v11'", $script);
        self::assertStringContainsString('promotedInferredPerimeterSuggestions.slice(0, perimeterInferenceBudget)', $script);
        self::assertStringContainsString('promotedPerimeterChains: promotedInferredPerimeterSuggestions.length', $script);
        self::assertStringContainsString("stage: 'perimeter-promotion'", $script);
        self::assertStringContainsString('!inferred[row][column] && !propagatedRecoveredSurface[row][column]', $script);
        self::assertStringContainsString('Phase IV.30.1G.5N — Inferred Perimeter Promotion & Emission Continuity ✅', $roadmap);
    }
}
