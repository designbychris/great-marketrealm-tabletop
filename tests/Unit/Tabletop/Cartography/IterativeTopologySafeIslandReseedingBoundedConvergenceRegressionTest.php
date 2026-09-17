<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class IterativeTopologySafeIslandReseedingBoundedConvergenceRegressionTest extends TestCase
{
    private string $script;

    protected function setUp(): void
    {
        parent::setUp();
        $this->script = (string) file_get_contents(__DIR__ . '/../../../../assets/js/tabletop.js');
    }

    public function test_iterative_reseeding_is_local_coherent_and_bounded(): void
    {
        self::assertStringContainsString('IV.30.1G.5Z.11 — Iterative Topology-Safe Island Reseeding & Bounded Convergence.', $this->script);
        self::assertStringContainsString('const maximumSecondarySeedGenerations=3;', $this->script);
        self::assertStringContainsString('const topologySafeSeedForRecord=(record) => {', $this->script);
        self::assertStringContainsString('if (remainingKeys.length < 2) return null;', $this->script);
        self::assertStringContainsString('for (let distance=1;distance<=2;distance+=1)', $this->script);
        self::assertStringContainsString('if (bridgeInk>=.62 || bridgeHorizontal || bridgeVertical)', $this->script);
        self::assertStringContainsString('for (let generation=2;generation<=maximumSecondarySeedGenerations;generation+=1)', $this->script);
        self::assertStringContainsString('record.iterativeSeedGeneration=generation;', $this->script);
        self::assertStringContainsString('const generationFloodRecovered=illustratedFloorFloodPass();', $this->script);
        self::assertStringContainsString("? 'generation-budget'", $this->script);
        self::assertStringContainsString("illustratedIterativeConvergenceReason='topology-safe-exhausted';", $this->script);
    }

    public function test_iterative_reseeding_preserves_existing_authority_and_review_safeguards(): void
    {
        self::assertStringContainsString('if (options.skipOcclusionRecovery !== true)', $this->script);
        self::assertStringContainsString('illustratedDisconnectedIslandRecords.forEach((record) => {', $this->script);
        self::assertStringContainsString('if (record.seeded || record.iterativeSeeded) return;', $this->script);
        self::assertStringContainsString('illustratedInteriorQualificationRejectedRecords.forEach((record) => {', $this->script);
        self::assertStringContainsString('const maximumPathVertices = 256;', $this->script);
        self::assertStringContainsString('maximumReviewSuggestions', $this->script);
        self::assertStringContainsString('illustratedIterativeGeneration2RecoveredCells', $this->script);
        self::assertStringContainsString('illustratedIterativeGeneration3RecoveredCells', $this->script);
        self::assertStringContainsString('illustratedIterativeConvergenceReason', $this->script);
    }
}
