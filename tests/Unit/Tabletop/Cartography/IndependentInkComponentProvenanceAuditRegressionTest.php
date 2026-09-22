<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class IndependentInkComponentProvenanceAuditRegressionTest extends TestCase
{
    public function test_g5z33_inspects_only_review_component_cells_without_promoting_walls(): void
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertIsString($source);
        self::assertStringContainsString('const componentProvenance = review.map((component) => {', $source);
        self::assertStringContainsString('const localCells = component.sampleCells.map((cell) =>', $source);
        self::assertStringContainsString('nearestRepresentedDistance:', $source);
        self::assertStringContainsString('nearbyCandidate += 1', $source);
        self::assertStringContainsString("classification: 'local-ink-provenance-review-not-wall-certified', wallCertified: false", $source);
        self::assertStringContainsString('independentInkComponentProvenanceAudit: {', $source);
        self::assertStringContainsString('wallCertified: false, admittedEdges: 0, restoredRuns: 0', $source);
        self::assertStringContainsString('G.5Z.33 ink provenance ·', $source);
        self::assertStringContainsString('cartographyAuditRuntimeWitness.dataset.cartographyInkProvenance', $source);
    }
}
