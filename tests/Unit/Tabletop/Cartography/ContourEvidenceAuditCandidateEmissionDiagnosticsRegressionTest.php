<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class ContourEvidenceAuditCandidateEmissionDiagnosticsRegressionTest extends TestCase
{
    public function test_keeper_can_audit_contour_evidence_without_making_it_authoritative(): void
    {
        $root = dirname(__DIR__, 4);
        $view = (string) file_get_contents($root . '/app/Tabletop/Views/chamber.php');
        $script = (string) file_get_contents($root . '/assets/js/tabletop.js');
        $css = (string) file_get_contents($root . '/assets/css/tabletop.css');
        $roadmap = (string) file_get_contents($root . '/ROADMAP.md');

        self::assertStringContainsString('<option value="audit">Evidence Audit · diagnostics</option>', $view);
        self::assertStringContainsString("evidenceModel: 'living-contour-evidence-audit-v10'", $script);
        self::assertStringContainsString("stage: 'semantic-classification'", $script);
        self::assertStringContainsString("stage: 'perimeter-inference'", $script);
        self::assertStringContainsString("'supplemental-budget-or-deduplication'", $script);
        self::assertStringContainsString('diagnostic only; never persisted', $script);
        self::assertStringContainsString('.gmrt-cartography-evidence-audit.is-rejected', $css);
        self::assertStringContainsString('Phase IV.30.1G.5M — Contour Evidence Audit & Candidate Emission Diagnostics ✅', $roadmap);
    }
}
