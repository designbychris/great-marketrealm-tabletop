<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class EvidenceAuditStatusOwnershipRegressionTest extends TestCase
{
    public function test_top_level_evidence_audit_owns_status_after_nested_contour_analysis(): void
    {
        $root = dirname(__DIR__, 4);
        $javascript = file_get_contents($root . '/assets/js/tabletop.js');
        $roadmap = file_get_contents($root . '/ROADMAP.md');

        self::assertIsString($javascript);
        self::assertIsString($roadmap);
        self::assertStringContainsString('IV.30.1G.5T.2 — Evidence Audit Status Ownership', $javascript);
        self::assertStringContainsString('const publishedEvidenceAudit = {', $javascript);
        self::assertStringContainsString("if (typeof options.onEvidenceAudit === 'function')", $javascript);
        self::assertStringContainsString('options.onEvidenceAudit(publishedEvidenceAudit);', $javascript);
        self::assertStringContainsString('let completedEvidenceAudit = null;', $javascript);
        self::assertStringContainsString('onEvidenceAudit: (audit) => { completedEvidenceAudit = audit; }', $javascript);
        self::assertStringContainsString('if (completedEvidenceAudit) cartographyEvidenceAudit = completedEvidenceAudit;', $javascript);
        self::assertStringContainsString('IV.30.1G.5T.2 — Evidence Audit Status Ownership', $roadmap);
    }
}
