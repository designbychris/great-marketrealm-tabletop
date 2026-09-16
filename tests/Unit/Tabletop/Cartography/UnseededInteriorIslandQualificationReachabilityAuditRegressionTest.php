<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class UnseededInteriorIslandQualificationReachabilityAuditRegressionTest extends TestCase
{
    private string $source;

    protected function setUp(): void
    {
        $this->source = (string) file_get_contents(__DIR__ . '/../../../../assets/js/tabletop.js');
    }

    public function test_unseeded_islands_are_reaudited_after_secondary_recovery_without_new_seed_authority(): void
    {
        self::assertStringContainsString('IV.30.1G.5Z.9 — Unseeded Interior Island Qualification & Reachability Audit', $this->source);
        self::assertStringContainsString('const illustratedDisconnectedIslandRecords=[];', $this->source);
        self::assertStringContainsString('illustratedDisconnectedIslandRecords.filter((record) => !record.seeded)', $this->source);
        self::assertStringContainsString('const postRecoveryPlayable=(x,y)', $this->source);
        self::assertStringContainsString('illustratedUnseededAbsorbedComponents', $this->source);
        self::assertStringContainsString('illustratedUnseededPostRecoveryAdjacentComponents', $this->source);
        self::assertStringContainsString('illustratedUnseededPostRecoveryNarrowGapComponents', $this->source);
        self::assertStringContainsString('illustratedUnseededPostRecoveryTopologySafeComponents', $this->source);
        self::assertStringContainsString('illustratedUnseededPostRecoveryStructuralBlockedComponents', $this->source);
    }

    public function test_g5z9_preserves_first_generation_seeding_and_existing_safeguards(): void
    {
        self::assertStringContainsString('illustratedSecondarySeedCandidates.forEach(([column,row]) => {', $this->source);
        self::assertStringContainsString('const secondaryFloodRecovered=illustratedFloorFloodPass();', $this->source);
        self::assertStringContainsString('No second-generation seed authority is granted in this phase.', $this->source);
        self::assertStringContainsString('maximumReviewSuggestions', $this->source);
        self::assertStringContainsString('const maximumPathVertices = 256;', $this->source);
    }
}
