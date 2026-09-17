<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class SurfaceBoundaryCorroborationFrontierArtefactAuditRegressionTest extends TestCase
{
    private string $script;

    protected function setUp(): void
    {
        parent::setUp();
        $this->script = (string) file_get_contents(__DIR__ . '/../../../../assets/js/tabletop.js');
    }

    public function test_completed_surface_edges_publish_local_corroboration_without_rejection(): void
    {
        self::assertStringContainsString('IV.30.1G.5Z.15 — Surface Boundary Corroboration & Frontier Artefact Audit.', $this->script);
        self::assertStringContainsString("let surfaceBoundaryCorroboration = 'unsupported-frontier';", $this->script);
        self::assertStringContainsString("surfaceBoundaryCorroboration = 'exact-structural';", $this->script);
        self::assertStringContainsString("surfaceBoundaryCorroboration = 'strong-local-ink';", $this->script);
        self::assertStringContainsString("surfaceBoundaryCorroboration = 'moderate-local-ink';", $this->script);
        self::assertStringContainsString('surfaceBoundaryCorroboration, surfaceBoundaryInk: boundaryInk,', $this->script);
        self::assertStringContainsString('illustratedSurfaceBoundaryOpenPaperFrontier', $this->script);
    }

    public function test_corroboration_audit_preserves_existing_boundary_and_capacity_safeguards(): void
    {
        self::assertStringContainsString('if (contourThresholdMatch(a,b))', $this->script);
        self::assertStringContainsString('reconstructedIllustratedSurfaceEdges.push({', $this->script);
        self::assertStringContainsString('const maximumSecondarySeedGenerations=10;', $this->script);
        self::assertStringContainsString('const maximumPathVertices = 256;', $this->script);
        self::assertStringContainsString('certifiedSurfaceOverlays', $this->script);
        self::assertStringContainsString('boundary corroboration ${audit.illustratedSurfaceBoundaryStructuralCorroborated || 0} exact structural', $this->script);
        self::assertStringContainsString('${audit.illustratedSurfaceBoundaryUnsupportedFrontier || 0} unsupported frontier', $this->script);
    }
    public function test_surface_structural_corroboration_uses_a_living_contour_local_lookup(): void
    {
        self::assertStringContainsString('IV.30.1G.5Z.15A — Surface Corroboration Runtime Scope Correction.', $this->script);
        self::assertStringContainsString('const surfaceStructuralByKey = new Map(', $this->script);
        self::assertStringContainsString('const surfaceStructuralEdge = (x1, y1, x2, y2)', $this->script);
        self::assertStringContainsString('Boolean(surfaceStructuralEdge(a.x, a.y, b.x, b.y))', $this->script);
        self::assertStringNotContainsString('Boolean(structuralEdge(a.x, a.y, b.x, b.y))', $this->script);
    }

}
