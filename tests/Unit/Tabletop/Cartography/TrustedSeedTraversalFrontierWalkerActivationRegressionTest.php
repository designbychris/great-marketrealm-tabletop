<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class TrustedSeedTraversalFrontierWalkerActivationRegressionTest extends TestCase
{
    public function test_trusted_seed_mask_is_consumed_by_frontier_walker_and_live_audit_is_not_overwritten(): void
    {
        $root = dirname(__DIR__, 4);
        $script = (string) file_get_contents($root . '/assets/js/tabletop.js');
        $roadmap = (string) file_get_contents($root . '/ROADMAP.md');

        self::assertStringContainsString('IV.30.1G.5T — Trusted Seed Traversal & Frontier Walker Activation', $script);
        self::assertStringContainsString('livingContourCandidates({ ...options, skipOcclusionRecovery: true })', $script);
        self::assertStringContainsString('options.evidenceAudit !== true || options.skipOcclusionRecovery === true', $script);
        self::assertStringContainsString('const adjacentPlayable = orthogonal.reduce', $script);
        self::assertStringContainsString('illustratedTraversalSeedsQueued', $script);
        self::assertStringContainsString('illustratedTraversalSeedsVisited', $script);
        self::assertStringContainsString('const traversalSeeds=[]', $script);
        self::assertStringContainsString('if (isPlayable(column,row)) traversalSeeds.push([column,row])', $script);
        self::assertStringContainsString('const examinedThisPass=new Set()', $script);
        self::assertStringContainsString('traversal seeds queued', $script);
        self::assertStringContainsString('traversal seeds visited', $script);
        self::assertStringContainsString('Phase IV.30.1G.5T — Trusted Seed Traversal & Frontier Walker Activation ✅', $roadmap);
    }
}
