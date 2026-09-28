<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class PropagationCrossingAuditRegressionTest extends TestCase
{
    public function test_g5z48_traces_first_admission_provenance_without_changing_recovery(): void
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertIsString($source);
        self::assertStringContainsString("IV.30.1G.5Z.48 — The Cartographer's Propagation Crossing Audit", $source);
        self::assertStringContainsString('const illustratedAdmissionProvenance = new Map();', $source);
        self::assertStringContainsString('const propagationCrossingAudit = (() => {', $source);
        self::assertStringContainsString("scope:'first-admission-recovery-provenance-plus-original-image-interface-ink-not-wall-certification'", $source);
        self::assertStringContainsString("'first-admission-interface-crossing-near-wall-band'", $source);
        self::assertStringContainsString("'playable-sides-share-recovery-ancestry-without-local-direct-crossing'", $source);
        self::assertStringContainsString("'playable-sides-have-independent-first-admission-roots'", $source);
        self::assertStringContainsString('interfaceInk:interfaceInk(record.parentColumn,record.parentRow,record.column,record.row)', $source);
        self::assertStringContainsString('recoveryVetoes:0', $source);
        self::assertStringContainsString('propagationCrossingAudit: propagationCrossingAudit,', $source);
        self::assertStringContainsString('G.5Z.48 propagation crossing unavailable', $source);
    }

    public function test_g5z48_preserves_certified_limits_and_existing_restoration_contracts(): void
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertIsString($source);
        self::assertStringContainsString('const maximumReviewSuggestions = 200;', $source);
        self::assertStringContainsString('const maximumPathVertices = 256;', $source);
        self::assertStringContainsString('surfaceBoundaryMicroGapRestored', $source);
        self::assertStringContainsString('surfaceBoundaryShortGapRestored', $source);
    }
}
