<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class StructuralEvidenceCorroborationRegressionTest extends TestCase
{
    private function root(string $relative): string
    {
        return dirname(__DIR__, 4) . '/' . ltrim($relative, '/');
    }

    public function test_hybrid_requires_artwork_corroboration_before_structural_authority(): void
    {
        $script = (string) file_get_contents($this->root('assets/js/tabletop.js'));
        self::assertStringContainsString('IV.30.1G.5B — Structural Evidence Corroboration', $script);
        self::assertStringContainsString('const structuralArtworkCorroboration = (item) => {', $script);
        self::assertStringContainsString('corroborated: livingAgreement || asymmetricWallBody', $script);
        self::assertStringContainsString('.filter((item) => item.structuralArtworkEvidence.corroborated)', $script);
    }

    public function test_grid_alignment_cannot_create_hybrid_structure_by_itself(): void
    {
        $script = (string) file_get_contents($this->root('assets/js/tabletop.js'));
        self::assertStringContainsString('The calibrated grid is a ruler, not artwork.', $script);
        self::assertStringContainsString('const livingContourCorroborates = (item) => {', $script);
        self::assertStringContainsString('const asymmetricWallBody = wallBodyDensity >= .115 && wallBodyDensity >= quietSideDensity * 1.45', $script);
        self::assertStringContainsString("structuralEvidenceCorroboration: item.structuralArtworkEvidence.livingAgreement ? 'living-contour' : 'wall-body-ink'", $script);
    }

    public function test_semantic_suppression_is_not_resurrected_by_uncorroborated_structural_reader(): void
    {
        $script = (string) file_get_contents($this->root('assets/js/tabletop.js'));
        self::assertStringContainsString('const connectedContours = livingContourCandidates({', $script);
        self::assertStringContainsString('connectPlayableFloor: true,', $script);
        self::assertStringContainsString('thresholdCandidates', $script);
        self::assertStringContainsString('const fallbackContours = livingContourCandidates({ thresholdCandidates });', $script);
        self::assertStringContainsString('const contourSegments = [];', $script);
        self::assertStringContainsString('confidence alone must never resurrect the grid', $script);
    }

    public function test_phase_is_documented(): void
    {
        $roadmap = (string) file_get_contents($this->root('ROADMAP.md'));
        $phase = (string) file_get_contents($this->root('docs/Roadmap/PHASE-IV.30.1G.5B.md'));
        self::assertStringContainsString('[x] **IV.30.1G.5B — Structural Evidence Corroboration**', $roadmap);
        self::assertStringContainsString('The Grid Is Not the Dungeon', $phase);
    }
}
