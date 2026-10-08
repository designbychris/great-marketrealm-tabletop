<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class CornerMissingConnectionRegressionTest extends TestCase
{
    public function test_corner_gap_survey_is_bounded_and_diagnostic_only(): void
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertIsString($source);
        self::assertStringContainsString("IV.30.1G.5Z.50Z.5 — The Corner's Missing Connection.", $source);
        self::assertStringContainsString('for(let ix=-4;ix<=4;ix++)for(let iy=-4;iy<=4;iy++)', $source);
        self::assertStringContainsString('completedSurfaceEdgeKeys.has(completedEdgeKey(other,end))', $source);
        self::assertStringContainsString('surfaceStructuralEdge(other.x,other.y,end.x,end.y)', $source);
        self::assertStringContainsString('illustratedPropagationBarrierCornerMissingConnectionReviews,', $source);
        self::assertStringContainsString('diagnostic-only;bounded-quarter-grid-endpoint-survey;no-snapping;no-wall-certification;no-geometry-mutation;G.5Z.50-veto-unchanged', $source);
    }
}
