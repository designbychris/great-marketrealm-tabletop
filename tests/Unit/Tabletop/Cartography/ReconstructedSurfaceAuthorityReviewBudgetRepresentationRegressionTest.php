<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class ReconstructedSurfaceAuthorityReviewBudgetRepresentationRegressionTest extends TestCase
{
    public function test_reconstructed_surface_geometry_can_extend_authority_without_becoming_object_201(): void
    {
        $root = dirname(__DIR__, 4);
        $script = (string) file_get_contents($root . '/assets/js/tabletop.js');
        $roadmap = (string) file_get_contents($root . '/ROADMAP.md');

        self::assertStringContainsString('IV.30.1G.5W — Reconstructed Surface Authority & Review-Budget Representation', $script);
        self::assertStringContainsString('living-contour-reconstructed-surface-authority-v12', $script);
        self::assertStringContainsString('const promotedReconstructedSurfaceSuggestions = promoteInferredPerimeterChains(reconstructedIllustratedSurfaceEdges)', $script);
        self::assertStringContainsString('reconstructedSurfaceAuthorityExtension: true', $script);
        self::assertStringContainsString('zero-additional-review-object-cost', $script);
        self::assertStringContainsString("appendByAuthority(remainingReconstructedSurfaceSuggestions, 'surface');", $script);
        self::assertStringContainsString('surface paths promoted', $script);
        self::assertStringContainsString('authority extensions', $script);
        self::assertStringContainsString('surface paths represented', $script);
        self::assertStringContainsString('Phase IV.30.1G.5W — Reconstructed Surface Authority & Review-Budget Representation ✅', $roadmap);
    }
}
