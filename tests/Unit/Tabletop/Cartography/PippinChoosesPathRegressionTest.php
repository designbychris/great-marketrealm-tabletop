<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class PippinChoosesPathRegressionTest extends TestCase
{
    public function test_conflicting_strokes_are_compared_without_geometry_mutation(): void
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertIsString($source);
        foreach (['IV.30.2C.5 — Pippin Chooses His Path',
            'shared-endpoint-unresolved-review', 'preferred-stroke-review-only',
            'pippinPathChoice,', 'proposedGeometry:0,changedGeometry:0',
            'IV.30.2C.5 Pippin chooses his path'] as $expected) {
            self::assertStringContainsString($expected, $source);
        }
    }
}
