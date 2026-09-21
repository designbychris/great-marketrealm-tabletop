<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class ResidualEndpointPairingConnectivityAuditRegressionTest extends TestCase
{
    public function test_g5z23_uses_connected_run_identity_and_does_not_promote_suppressed_edges(): void
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertIsString($source);
        self::assertStringContainsString('const suppressedRunByIndex = new Map()', $source);
        self::assertStringContainsString('stillSuppressed[candidate].surfaceComponentId === edge.surfaceComponentId', $source);
        self::assertStringContainsString('suppressedRunIds: [...(suppressedRunsByPoint.get(', $source);
        self::assertStringContainsString("'two-distinct-endpoints'", $source);
        self::assertStringContainsString("'ambiguous-multiple-endpoints'", $source);
        self::assertStringContainsString('residualUnmatchedEndpointIds', $source);
        self::assertStringContainsString("data-audit-suppressed-run", $source);
        self::assertStringContainsString("G.5Z.23 connectivity", $source);
        self::assertLessThan(strpos($source, 'const residualRunPairings ='), strpos($source, 'const residualTerminations = []'));
    }
}
