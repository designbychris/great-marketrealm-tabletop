<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class ShortRunCorroboratedBoundaryContinuityRestorationRegressionTest extends TestCase
{
    private function source(): string
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertIsString($source);
        return $source;
    }

    public function test_g5z21_is_a_separate_bounded_short_run_class(): void
    {
        $source = $this->source();
        self::assertStringContainsString('const shortGapEligible = boundedTerminals >= 2', $source);
        self::assertStringContainsString('&& !branchAdjacent', $source);
        self::assertStringContainsString('&& runLength >= 5', $source);
        self::assertStringContainsString('&& runLength <= 8', $source);
        self::assertStringContainsString('&& shortRunPublishedTravelTenths <= 3', $source);
        self::assertStringContainsString('const microGapEligible = boundedTerminals >= 2 && !branchAdjacent && runLength <= 2;', $source);
    }

    public function test_g5z21_restores_only_original_edges_and_keeps_review_limits(): void
    {
        $source = $this->source();
        self::assertStringContainsString('const restored = edges[index];', $source);
        self::assertStringContainsString('surfaceBoundaryShortGapRestored: true', $source);
        self::assertStringContainsString("'g5z21-short-run-continuity'", $source);
        self::assertStringContainsString('const maximumReviewSuggestions = 200;', $source);
        self::assertStringContainsString('const maximumPathVertices = 256;', $source);
        self::assertStringContainsString('short-run restoration ${audit.illustratedSurfaceShortGapEligibleRuns || 0} eligible runs', $source);
    }
}
