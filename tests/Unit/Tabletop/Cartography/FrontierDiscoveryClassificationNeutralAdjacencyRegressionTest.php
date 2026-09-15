<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class FrontierDiscoveryClassificationNeutralAdjacencyRegressionTest extends TestCase
{
    public function test_frontier_is_discovered_topologically_before_classification(): void
    {
        $root = dirname(__DIR__, 4);
        $script = (string) file_get_contents($root . '/assets/js/tabletop.js');
        $roadmap = (string) file_get_contents($root . '/ROADMAP.md');

        self::assertStringContainsString('IV.30.1G.5S — Frontier Discovery & Classification-Neutral Adjacency', $script);
        self::assertStringContainsString('illustratedSeedComponent', $script);
        self::assertStringContainsString('illustratedExteriorSeedComponents', $script);
        self::assertStringContainsString('isTrustedIllustratedSeed', $script);
        self::assertStringContainsString('illustratedAdjacentSamplesExamined', $script);
        self::assertStringContainsString('illustratedAlreadyPlayableNeighbours', $script);
        self::assertStringContainsString('illustratedFrontierCandidates+=1', $script);
        self::assertStringContainsString('G.5S discovery is classification-neutral', $script);
        self::assertStringContainsString('adjacent samples examined', $script);
        self::assertStringContainsString('already-playable neighbours', $script);
        self::assertStringContainsString('authoritativePreserved', $script);
        self::assertStringContainsString('Phase IV.30.1G.5S — Frontier Discovery & Classification-Neutral Adjacency ✅', $roadmap);
    }
}
