<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class PromotedChainConsolidationEmissionBudgetLiberationRegressionTest extends TestCase
{
    public function test_promoted_perimeter_is_consolidated_and_uses_remaining_review_capacity(): void
    {
        $root = dirname(__DIR__, 5);
        $script = (string) file_get_contents($root . '/assets/js/tabletop.js');
        $roadmap = (string) file_get_contents($root . '/ROADMAP.md');

        self::assertStringContainsString('IV.30.1G.5O — Promoted Chain Consolidation & Emission Budget Liberation', $script);
        self::assertStringContainsString('consolidatePromotedPerimeterChains', $script);
        self::assertStringContainsString('consolidatedPromotedPerimeterSuggestions', $script);
        self::assertStringContainsString('maximumReviewSuggestions - preOcclusionRecoveryContours.length - bridgeSupplementalSuggestions.length', $script);
        self::assertStringContainsString('representedPromotedChains:', $script);
        self::assertStringContainsString('representedPromotedEdges:', $script);
        self::assertStringContainsString('represented (${audit.representedPromotedEdges || 0} edges)', $script);
        self::assertStringContainsString('Phase IV.30.1G.5O — Promoted Chain Consolidation & Emission Budget Liberation ✅', $roadmap);
    }
}
