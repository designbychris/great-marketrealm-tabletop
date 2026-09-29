<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class PropagationCrossingProvenanceAuditRegressionTest extends TestCase
{
    public function test_g5z49_distinguishes_exact_bounded_crossings_bypasses_and_independent_roots(): void
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertIsString($source);
        self::assertStringContainsString("IV.30.1G.5Z.49 — The Cartographer's Propagation Crossing Provenance Audit", $source);
        self::assertStringContainsString('const propagationCrossingProvenanceAudit = (() => {', $source);
        self::assertStringContainsString("'direct-first-admission-crossing-of-bounded-wall-band'", $source);
        self::assertStringContainsString("'shared-ancestry-bypass-without-bounded-direct-crossing'", $source);
        self::assertStringContainsString("'independent-recovery-roots-converged-in-completed-surface'", $source);
        self::assertStringContainsString("'canonical-or-unresolved-provenance-origin'", $source);
        self::assertStringContainsString('admissionRoute:admissionRoute(record)', $source);
        self::assertStringContainsString('parentSideCells:', $source);
        self::assertStringContainsString('childSideCells:', $source);
        self::assertStringContainsString('interfaceInk:interfaceInk(record)', $source);
        self::assertStringContainsString('propagationCrossingProvenanceAudit: propagationCrossingProvenanceAudit,', $source);
        self::assertStringContainsString('G.5Z.49 crossing provenance unavailable', $source);
    }

    public function test_g5z49_is_diagnostic_only_and_preserves_certified_limits(): void
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertIsString($source);
        self::assertStringContainsString('wallCertified:false,admittedEdges:0,restoredRuns:0,recoveryVetoes:0', $source);
        self::assertStringContainsString('const maximumReviewSuggestions = 200;', $source);
        self::assertStringContainsString('const maximumPathVertices = 256;', $source);
        self::assertStringContainsString('surfaceBoundaryMicroGapRestored', $source);
        self::assertStringContainsString('surfaceBoundaryShortGapRestored', $source);
    }
}
