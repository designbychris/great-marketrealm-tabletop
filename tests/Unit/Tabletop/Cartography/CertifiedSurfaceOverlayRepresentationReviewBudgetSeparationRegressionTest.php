<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class CertifiedSurfaceOverlayRepresentationReviewBudgetSeparationRegressionTest extends TestCase
{
    private string $source;

    protected function setUp(): void
    {
        $this->source = (string) file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
    }

    public function test_certified_surface_overlays_are_separate_from_the_200_object_review_budget(): void
    {
        self::assertStringContainsString('IV.30.1G.5Z.13 — Certified Surface Overlay Representation & Review-Budget Separation', $this->source);
        self::assertStringContainsString('const maximumReviewSuggestions = 200;', $this->source);
        self::assertStringContainsString('const certifiedSurfaceOverlaySuggestions = remainingReconstructedSurfaceSuggestions.map', $this->source);
        self::assertStringContainsString('certifiedSurfaceOverlay: true', $this->source);
        self::assertStringContainsString("'separate-from-review-suggestion-budget'", $this->source);
        self::assertStringContainsString('const reviewBudgetSuggestions = arbitratedSuggestions.slice();', $this->source);
        self::assertStringContainsString('pathSuggestions = reviewBudgetSuggestions.concat(certifiedSurfaceOverlaySuggestions);', $this->source);
        self::assertStringContainsString('const reconstructedSurfaceCapacityRejectedChains = 0;', $this->source);
    }

    public function test_overlay_geometry_remains_complete_without_weakening_existing_safety_caps(): void
    {
        self::assertStringContainsString('const maximumPathVertices = 256;', $this->source);
        self::assertStringContainsString('const reconstructedSurfaceFinalSegmentKeys = new Set(pathSuggestions.flatMap(authoritativeSegmentsFor));', $this->source);
        self::assertStringContainsString('if (reviewBudgetSuggestions.length > maximumReviewSuggestions)', $this->source);
        self::assertStringContainsString('Array.from(merged.values()).concat(certifiedSurfaceOverlaySuggestions)', $this->source);
        self::assertStringContainsString('reconstructedSurfaceOverlayChains,', $this->source);
        self::assertStringContainsString('reconstructedSurfaceOverlayEdges,', $this->source);
        self::assertStringContainsString('certifiedSurfaceOverlaysEmitted:', $this->source);
        self::assertStringContainsString('Diagnostic marks are never saved.', $this->source);
    }
}
