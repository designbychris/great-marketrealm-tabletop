<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class ExteriorFrontierProvenancePlayableSurfaceSeparationAuditRegressionTest extends TestCase
{
    public function test_g5z29_classifies_original_inside_outside_provenance_without_admitting_walls(): void
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertIsString($source);
        self::assertStringContainsString('const residualExteriorFrontierAudit = (() => {', $source);
        self::assertStringContainsString('edge.insideColumn + edge.outsideDx', $source);
        self::assertStringContainsString('edge.insideRow + edge.outsideDy', $source);
        self::assertStringContainsString("'recovered-surface-frontier-not-illustrated-wall'", $source);
        self::assertStringContainsString("'incomplete-cell-provenance'", $source);
        self::assertStringContainsString('wallCertification: false, admittedEdges: 0, restoredRuns: 0', $source);
        self::assertStringContainsString('                    residualExteriorFrontierAudit,', $source);
        self::assertStringContainsString('G.5Z.29 frontier ·', $source);
        self::assertStringContainsString('cartographyAuditRuntimeWitness.dataset.cartographyFrontier', $source);
    }
}
