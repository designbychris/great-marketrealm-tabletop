<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class WallMustKeepItsShapeRegressionTest extends TestCase
{
    public function test_g5z50i_audits_bounded_route_shape_without_changing_production_barrier(): void
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertIsString($source);
        self::assertStringContainsString('IV.30.1G.5Z.50I — The Wall Must Keep Its Shape.', $source);
        self::assertStringContainsString('const barrierShapeBestRoute=', $source);
        self::assertStringContainsString('trackChanges', $source);
        self::assertStringContainsString('reversals', $source);
        self::assertStringContainsString('maxDisplacement', $source);
        self::assertStringContainsString('edgeHugging', $source);
        self::assertStringContainsString('ambiguousSteps', $source);
        self::assertStringContainsString('competingRoutes', $source);
        self::assertStringContainsString("'candidate-floor-open','control-floor-both'", $source);
        self::assertStringContainsString('G.5Z.50I wall must keep its shape', $source);
        self::assertStringContainsString('fixed-corridor-route-shape-not-wall-certification;no-recovery-replay;G.5Z.50-veto-unchanged', $source);
        self::assertStringContainsString('const maximumReviewSuggestions = 200;', $source);
        self::assertStringContainsString('const reconstructedSurfaceReviewCeiling = maximumReviewSuggestions;', $source);
        self::assertStringContainsString('const maximumPathVertices = 256;', $source);
    }
}
