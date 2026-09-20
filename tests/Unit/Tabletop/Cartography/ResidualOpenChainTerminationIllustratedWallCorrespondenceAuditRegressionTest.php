<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class ResidualOpenChainTerminationIllustratedWallCorrespondenceAuditRegressionTest extends TestCase
{
    private function source(): string
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertIsString($source);
        return $source;
    }

    public function test_g5z22_audits_final_open_paths_without_admitting_geometry(): void
    {
        $source = $this->source();
        self::assertStringContainsString('const residualTerminations = [];', $source);
        self::assertStringContainsString('promotedReconstructedSurfaceSuggestions.forEach((path, pathIndex)', $source);
        self::assertStringContainsString('retainedSurfaceEdgeKeys.has(edge.key)', $source);
        self::assertStringContainsString('openingClassification: \'unresolved\'', $source);
        self::assertStringContainsString('residualTerminations,', $source);
        self::assertStringContainsString('const maximumReviewSuggestions = 200;', $source);
    }

    public function test_g5z22_markers_are_audit_only_and_not_draft_suggestions(): void
    {
        $source = $this->source();
        self::assertStringContainsString("cartographyDetail?.value === 'audit' && Array.isArray(cartographyEvidenceAudit?.residualTerminations)", $source);
        self::assertStringContainsString("marker.setAttribute('pointer-events', 'none');", $source);
        self::assertStringContainsString('fragment.append(marker);', $source);
        self::assertStringContainsString('cartographySuggestionLayer.replaceChildren();', $source);
    }
}
