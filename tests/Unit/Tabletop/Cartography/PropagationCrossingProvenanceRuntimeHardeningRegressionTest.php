<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class PropagationCrossingProvenanceRuntimeHardeningRegressionTest extends TestCase
{
    public function test_g5z49b_post_flood_component_walk_owns_its_traversal_scope(): void
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertIsString($source);
        self::assertStringContainsString("IV.30.1G.5Z.49B — The Cartographer's Provenance Runtime Hardening", $source);
        self::assertStringContainsString('const decorationRejectOrthogonal = [[-1,0],[1,0],[0,-1],[0,1]];', $source);
        self::assertStringContainsString('const decorationRejectInBounds = (x,y)', $source);
        self::assertStringContainsString('decorationRejectOrthogonal.forEach(([dx,dy], directionIndex) => {', $source);
        self::assertStringContainsString('if (!decorationRejectInBounds(nx,ny)) return;', $source);
    }

    public function test_g5z49b_preserves_bounded_witness_point_and_guards_provenance_coordinates(): void
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertIsString($source);
        self::assertStringContainsString('point: { x: review.point.x, y: review.point.y },', $source);
        self::assertStringContainsString("classification:'bounded-review-point-unavailable'", $source);
        self::assertStringContainsString("rootType:'provenance-coordinate-unavailable'", $source);
        self::assertStringContainsString('[record.parentColumn,record.parentRow,record.column,record.row].every(Number.isFinite)', $source);
        self::assertStringNotContainsString('window.orthogonal', $source);
        self::assertStringNotContainsString('globalThis.orthogonal', $source);
    }

    public function test_g5z49b_remains_diagnostic_only_and_preserves_certified_limits(): void
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertIsString($source);
        self::assertStringContainsString('wallCertified:false,admittedEdges:0,restoredRuns:0,recoveryVetoes:0', $source);
        self::assertStringContainsString('const maximumReviewSuggestions = 200;', $source);
        self::assertStringContainsString('const maximumPathVertices = 256;', $source);
        self::assertStringContainsString('surfaceBoundaryMicroGapRestored', $source);
        self::assertStringContainsString('surfaceBoundaryShortGapRestored', $source);
    }
}
