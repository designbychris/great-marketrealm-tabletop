<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class CartographyEvidenceLedgerRegressionTest extends TestCase
{
    public function test_g5z37_ledger_keeps_local_completion_separate_from_whole_illustration_certification(): void
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertIsString($source);
        self::assertStringContainsString('IV.30.1G.5Z.37 — Evidence ledger', $source);
        self::assertStringContainsString("scope: 'bounded-cartography-evidence-not-whole-illustration'", $source);
        self::assertStringContainsString('sourcesPresent: ledgerSourcesPresent', $source);
        self::assertStringContainsString('quietNotWalls: coverage.quietUnrepresentedEdges', $source);
        self::assertStringContainsString('complete: disposition.localReviewsComplete', $source);
        self::assertStringContainsString("'whole-illustration-wall-coverage-unverified'", $source);
        self::assertStringContainsString("'quiet-frontier-is-not-wall-evidence'", $source);
        self::assertStringContainsString('wholeIllustrationCoverageCertified: false,', $source);
        self::assertStringContainsString('missingWallsCertified: false, wallCertified: false,', $source);
        self::assertStringContainsString('admittedEdges: 0, restoredRuns: 0', $source);
        self::assertStringContainsString('dataset.cartographyEvidenceLedger', $source);
        self::assertStringContainsString('G.5Z.37 evidence ledger ·', $source);
    }
}
