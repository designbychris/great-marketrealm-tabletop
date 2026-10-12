<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class PippinFollowsStrokeRegressionTest extends TestCase
{
    public function test_fixed_corridor_stroke_trace_is_diagnostic_only(): void
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertIsString($source);
        foreach (['IV.30.2C.4 — Pippin Follows the Stroke',
            "classification === 'directional-ink-review-only'", 'tracks = [-.375,-.25,-.125,0,.125,.25,.375]',
            'parallel-hatching-competes-review', 'stroke-plausible-endpoint-conflict-review',
            'selectedTracks:chosen', 'proposedGeometry:0,changedGeometry:0',
            'pippinStrokeTrace,', 'IV.30.2C.4 Pippin follows the stroke'] as $expected) {
            self::assertStringContainsString($expected, $source);
        }
    }
}
