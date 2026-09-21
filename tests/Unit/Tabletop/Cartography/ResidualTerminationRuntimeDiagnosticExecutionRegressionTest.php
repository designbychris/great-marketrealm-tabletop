<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class ResidualTerminationRuntimeDiagnosticExecutionRegressionTest extends TestCase
{
    public function test_g5z22b_runtime_witness_is_independent_of_legacy_status_and_checks_rendered_markers(): void
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertIsString($source);
        self::assertStringContainsString("cartographyAuditRuntimeWitness.dataset.cartographyAuditRuntime = 'G.5Z.22B'", $source);
        self::assertStringContainsString("reportCartographyAuditRuntime('updated JavaScript executing · audit started')", $source);
        self::assertStringContainsString("audit callback ${completedEvidenceAudit ? 'received' : 'MISSING'}", $source);
        self::assertStringContainsString("querySelectorAll('[data-audit-termination]')", $source);
        self::assertStringContainsString("cartographyAuditRuntimeWitness.style.display = 'none'", $source);
    }
}
