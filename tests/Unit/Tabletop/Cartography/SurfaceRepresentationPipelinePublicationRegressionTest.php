<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class SurfaceRepresentationPipelinePublicationRegressionTest extends TestCase
{
    public function test_reconstructed_surface_edges_prevent_the_historical_early_return_and_reach_representation(): void
    {
        $root = dirname(__DIR__, 4);
        $script = (string) file_get_contents($root . '/assets/js/tabletop.js');
        $roadmap = (string) file_get_contents($root . '/ROADMAP.md');

        self::assertStringContainsString('IV.30.1G.5W.1 — Surface Representation Pipeline Publication', $script);
        self::assertStringContainsString('&& reconstructedIllustratedSurfaceEdges.length === 0', $script);
        self::assertStringContainsString('const promotedReconstructedSurfaceSuggestions = promoteInferredPerimeterChains(reconstructedIllustratedSurfaceEdges)', $script);
        self::assertStringContainsString("appendByAuthority(remainingReconstructedSurfaceSuggestions, 'surface');", $script);
        self::assertStringContainsString('reconstructedSurfacePromotedChains: promotedReconstructedSurfaceSuggestions.length', $script);
        self::assertStringContainsString('reconstructedSurfaceRepresentedChains: representedReconstructedSurfaceKeys.size', $script);
        self::assertStringContainsString('Phase IV.30.1G.5W.1 — Surface Representation Pipeline Publication ✅', $roadmap);
    }
}
