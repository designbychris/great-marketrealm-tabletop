<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class CoincidentEndpointContinuityLocalPathAssemblyAuditRegressionTest extends TestCase
{
    public function test_g5z24_reports_exact_coincidences_without_merging_or_admitting_suppressed_edges(): void
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertIsString($source);
        self::assertStringContainsString('const residualCoincidentEndpointGroups = []', $source);
        self::assertStringContainsString('residualTerminationPointKey(record.point)', $source);
        self::assertStringContainsString("'ambiguous-multiple-endpoints'", $source);
        self::assertStringContainsString("'overlapping-local-edge'", $source);
        self::assertStringContainsString("'source-provenance-unverified'", $source);
        self::assertStringContainsString("'vertex-cap-split-review'", $source);
        self::assertStringContainsString('diagnosticOnly: true', $source);
        self::assertStringContainsString('G.5Z.24 coincidence', $source);
        self::assertStringContainsString('residualCoincidentEndpointGroups,', $source);
        self::assertLessThan(strpos($source, 'const residualCoincidentEndpointGroups = []'), strpos($source, 'const residualTerminations = []'));
        self::assertLessThan(strpos($source, 'const residualRunPairings ='), strpos($source, 'const residualCoincidentEndpointGroups = []'));
    }
}
