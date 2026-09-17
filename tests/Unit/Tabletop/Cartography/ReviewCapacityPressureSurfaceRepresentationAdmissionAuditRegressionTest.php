<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class ReviewCapacityPressureSurfaceRepresentationAdmissionAuditRegressionTest extends TestCase
{
    private string $script;

    protected function setUp(): void
    {
        parent::setUp();
        $this->script = (string) file_get_contents(__DIR__ . '/../../../../assets/js/tabletop.js');
    }

    public function test_review_capacity_pressure_accounts_for_requested_admitted_and_rejected_surface_geometry(): void
    {
        self::assertStringContainsString('IV.30.1G.5Z.12 — Review Capacity Pressure & Surface Representation Admission Audit.', $this->script);
        self::assertStringContainsString('const reconstructedSurfaceReviewCeiling = maximumReviewSuggestions;', $this->script);
        self::assertStringContainsString('const reconstructedSurfaceAuthorityOccupancy = arbitratedSuggestions.length;', $this->script);
        self::assertStringContainsString('const reconstructedSurfaceSlotsAvailable = Math.max(0, maximumReviewSuggestions - reconstructedSurfaceAuthorityOccupancy);', $this->script);
        self::assertStringContainsString('const reconstructedSurfaceRequestedNovelChains = remainingReconstructedSurfaceSuggestions.length;', $this->script);
        self::assertStringContainsString('const reconstructedSurfaceCapacityRejectedSuggestions = remainingReconstructedSurfaceSuggestions.filter(', $this->script);
        self::assertStringContainsString('const reconstructedSurfaceCapacityRejectedEdges = reconstructedSurfaceCapacityRejectedSuggestions.reduce(', $this->script);
        self::assertStringContainsString('reconstructedSurfaceCapacityRejectedClosedChains', $this->script);
        self::assertStringContainsString('reconstructedSurfaceCapacityRejectedOpenChains', $this->script);
    }

    public function test_capacity_audit_is_diagnostic_only_and_preserves_existing_safety_boundaries(): void
    {
        self::assertStringContainsString('Do not raise the ceiling,', $this->script);
        self::assertStringContainsString('const maximumReviewSuggestions = 200;', $this->script);
        self::assertStringContainsString('const maximumPathVertices = 256;', $this->script);
        self::assertStringContainsString('reconstructedSurfaceCapacityRejectedVertexCapSegments', $this->script);
        self::assertStringContainsString('reconstructedSurfaceCapacityRejectedEndpointContiguous', $this->script);
        self::assertStringContainsString('reconstructedSurfaceCapacityRejectedMergeEligible', $this->script);
        self::assertStringContainsString('rejectedPoints.length + representedPoints.length - 1 <= maximumPathVertices', $this->script);
        self::assertStringContainsString('reconstructedSurfaceCapacityRejectedIndependent', $this->script);
        self::assertStringContainsString('appendByAuthority(remainingReconstructedSurfaceSuggestions, \'surface\');', $this->script);
    }
}
