<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class FrontierAdmissionAccountingIllustratedNeighbourQualificationRegressionTest extends TestCase
{
    public function test_every_frontier_candidate_is_accounted_for_before_any_threshold_is_relaxed(): void
    {
        $root = dirname(__DIR__, 4);
        $script = (string) file_get_contents($root . '/assets/js/tabletop.js');
        $roadmap = (string) file_get_contents($root . '/ROADMAP.md');

        self::assertStringContainsString('IV.30.1G.5U — Frontier Admission Accounting & Illustrated Neighbour Qualification', $script);
        self::assertStringContainsString('illustratedFrontierInteriorSupportRejects', $script);
        self::assertStringContainsString('illustratedFrontierDecorationRejects', $script);
        self::assertStringContainsString('illustratedFrontierNeighbourQualified', $script);
        self::assertStringContainsString('illustratedFrontierDecorationQualified', $script);
        self::assertStringContainsString('illustratedFrontierUnaccounted: Math.max(0, illustratedFrontierCandidates - illustratedFrontierAdmitted', $script);
        self::assertStringContainsString('interior-support rejects', $script);
        self::assertStringContainsString('decoration rejects', $script);
        self::assertStringContainsString('unaccounted frontier', $script);
        self::assertStringContainsString('if (!localInteriorSupport) { illustratedFrontierInteriorSupportRejects+=1; continue; }', $script);
        self::assertStringContainsString('if (!inkIsPlausibleDecoration) { illustratedFrontierDecorationRejects+=1; continue; }', $script);
        self::assertStringContainsString('Phase IV.30.1G.5U — Frontier Admission Accounting & Illustrated Neighbour Qualification ✅', $roadmap);
    }
}
