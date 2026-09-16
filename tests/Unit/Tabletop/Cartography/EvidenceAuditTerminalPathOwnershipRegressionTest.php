<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class EvidenceAuditTerminalPathOwnershipRegressionTest extends TestCase
{
    public function test_top_level_audit_does_not_exit_before_traversal_telemetry_is_published(): void
    {
        $root = dirname(__DIR__, 4);
        $javascript = file_get_contents($root . '/assets/js/tabletop.js');
        $roadmap = file_get_contents($root . '/ROADMAP.md');

        self::assertIsString($javascript);
        self::assertIsString($roadmap);
        self::assertStringContainsString('IV.30.1G.5T.3 — Evidence Audit Terminal-Path Ownership', $javascript);
        self::assertStringContainsString("&& !(options.evidenceAudit === true && options.skipOcclusionRecovery !== true)", $javascript);
        self::assertStringContainsString('return preOcclusionRecoveryContours;', $javascript);
        self::assertStringContainsString('publishContourEvidenceAudit(mergedSuggestions);', $javascript);
        self::assertStringContainsString('IV.30.1G.5T.3 — Evidence Audit Terminal-Path Ownership', $roadmap);
    }
}
