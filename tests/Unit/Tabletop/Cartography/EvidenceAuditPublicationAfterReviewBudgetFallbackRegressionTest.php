<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class EvidenceAuditPublicationAfterReviewBudgetFallbackRegressionTest extends TestCase
{
    public function test_top_level_evidence_audit_survives_review_budget_fallback_and_recursive_baseline_stays_silent(): void
    {
        $root = dirname(__DIR__, 5);
        $javascript = file_get_contents($root . '/assets/js/tabletop.js');

        self::assertIsString($javascript);
        self::assertStringContainsString('IV.30.1G.5T.1 — Evidence Audit Publication After Review-Budget Fallback', $javascript);
        self::assertStringContainsString('if (options.evidenceAudit === true && options.skipOcclusionRecovery !== true)', $javascript);
        self::assertStringContainsString('pathSuggestions = preOcclusionRecoveryContours;', $javascript);
        self::assertStringContainsString("if (options.evidenceAudit !== true || options.skipOcclusionRecovery === true) return;", $javascript);
        self::assertStringContainsString('illustratedTraversalSeedsQueued', $javascript);
        self::assertStringContainsString('illustratedAdjacentSamplesExamined', $javascript);
    }
}
