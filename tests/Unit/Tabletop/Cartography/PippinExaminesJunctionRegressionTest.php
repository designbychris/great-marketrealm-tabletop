<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class PippinExaminesJunctionRegressionTest extends TestCase
{
    public function test_junction_review_is_diagnostic_and_preserves_geometry(): void
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertIsString($source);
        foreach (['IV.30.2C.7 — Pippin Examines the Junction',
            'collinear-rival-strokes-not-a-branch-review',
            'possible-branch-unproven-review',
            'illustrated-stroke-without-structural-proof-review',
            'completeTriangle', 'pippinJunctionExamination,',
            'proposedGeometry:0,changedGeometry:0',
            'IV.30.2C.7 Pippin examines the junction'] as $expected) {
            self::assertStringContainsString($expected, $source);
        }
    }
}
