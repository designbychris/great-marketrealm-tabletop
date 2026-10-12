<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class PippinFindsRightHandshakeRegressionTest extends TestCase
{
    public function test_handshake_remains_diagnostic_and_exposes_conflicting_junctions(): void
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertIsString($source);
        foreach (['IV.30.2C.6 — Pippin Finds the Right Handshake',
            'endpoint-direction-disagrees-review', 'branching-junction-unproven-review',
            'keeper-handshake-unresolved-review', 'pippinHandshake,',
            'proposedGeometry:0,changedGeometry:0',
            'IV.30.2C.6 Pippin finds the right handshake'] as $expected) {
            self::assertStringContainsString($expected, $source);
        }
    }
}
