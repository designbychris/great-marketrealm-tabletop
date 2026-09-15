<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class FloorSeedExpansionIllustratedFrontierAdmissionRegressionTest extends TestCase
{
    public function test_frontier_admission_is_topological_audited_and_preserves_authority(): void
    {
        $root = dirname(__DIR__, 4);
        $script = (string) file_get_contents($root . '/assets/js/tabletop.js');
        $roadmap = (string) file_get_contents($root . '/ROADMAP.md');

        self::assertStringContainsString('IV.30.1G.5R — Floor Seed Expansion & Illustrated Frontier Admission', $script);
        self::assertStringContainsString('illustratedFloorSeeds', $script);
        self::assertStringContainsString('illustratedFrontierCandidates', $script);
        self::assertStringContainsString('illustratedFrontierAdmitted', $script);
        self::assertStringContainsString('illustratedFrontierStructuralRejects', $script);
        self::assertStringContainsString('illustratedFrontierExteriorRejects', $script);
        self::assertStringContainsString('const exteriorLike', $script);
        self::assertStringContainsString('horizontalStructuralBand || verticalStructuralBand', $script);
        self::assertStringContainsString('authoritativePreserved', $script);
        self::assertStringContainsString('floor seeds', $script);
        self::assertStringContainsString('frontier candidates', $script);
        self::assertStringContainsString('frontier admitted', $script);
        self::assertStringContainsString('structural rejects', $script);
        self::assertStringContainsString('exterior rejects', $script);
        self::assertStringContainsString('Phase IV.30.1G.5R — Floor Seed Expansion & Illustrated Frontier Admission ✅', $roadmap);
    }
}
