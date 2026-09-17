<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class IterativeRecoveryTopologySafeConvergenceRegressionTest extends TestCase
{
    private string $script;

    protected function setUp(): void
    {
        parent::setUp();
        $this->script = (string) file_get_contents(__DIR__ . '/../../../../assets/js/tabletop.js');
    }

    public function test_recovery_continues_to_topology_safe_exhaustion_with_emergency_guard(): void
    {
        self::assertStringContainsString('IV.30.1G.5Z.14 — Iterative Recovery to Topology-Safe Convergence.', $this->script);
        self::assertStringContainsString('const maximumSecondarySeedGenerations=10;', $this->script);
        self::assertStringContainsString('for (let generation=2;generation<=maximumSecondarySeedGenerations;generation+=1)', $this->script);
        self::assertStringContainsString("illustratedIterativeConvergenceReason='topology-safe-exhausted';", $this->script);
        self::assertStringContainsString("? 'generation-budget'", $this->script);
        self::assertStringContainsString('const illustratedIterativeGenerationAudit=[];', $this->script);
        self::assertStringContainsString('illustratedIterativeGenerationAudit.push({ generation, candidates:generationCandidates.length, seeds:generationSeeds, recovered:generationRecovered });', $this->script);
    }

    public function test_convergence_extension_keeps_local_topology_and_representation_safeguards(): void
    {
        self::assertStringContainsString('if (remainingKeys.length < 2) return null;', $this->script);
        self::assertStringContainsString('for (let distance=1;distance<=2;distance+=1)', $this->script);
        self::assertStringContainsString('if (bridgeInk>=.62 || bridgeHorizontal || bridgeVertical)', $this->script);
        self::assertStringContainsString('const generationFloodRecovered=illustratedFloorFloodPass();', $this->script);
        self::assertStringContainsString('const maximumPathVertices = 256;', $this->script);
        self::assertStringContainsString('const maximumReviewSuggestions', $this->script);
        self::assertStringContainsString('certifiedSurfaceOverlays', $this->script);
        self::assertStringContainsString('illustratedIterativeGenerationAudit: illustratedIterativeGenerationAudit.map((entry) => ({...entry}))', $this->script);
    }
}
