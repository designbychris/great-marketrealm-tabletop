<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class CertificateMeetsBoundaryRegressionTest extends TestCase
{
    private function source(): string
    {
        return (string) file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
    }

    public function test_only_certified_exact_corner_is_examined_against_existing_boundary(): void
    {
        $source = $this->source();
        self::assertStringContainsString('IV.30.1G.5Z.50Z.2 — The Certificate Meets the Boundary.', $source);
        self::assertStringContainsString('certificate.certified?certificate.cornerCells:[]', $source);
        self::assertStringContainsString('completedSurfaceEdgeKeys.has(edgeKey)', $source);
        self::assertStringContainsString('surfaceStructuralEdge(a.x,a.y,b.x,b.y)', $source);
        self::assertStringContainsString('corroborated.length===proposed.length', $source);
    }

    public function test_audit_does_not_promote_uncertified_geometry(): void
    {
        $source = $this->source();
        self::assertStringContainsString('topology-closure-without-certified-boundary-review', $source);
        self::assertStringContainsString('illustratedPropagationBarrierCornerBoundaryUncertified,', $source);
        self::assertStringContainsString('diagnostic-only;exact-four-neighbour-boundary-counterfactual;no-wall-admission;no-recovery-replay;no-geometry-mutation', $source);
    }
}
