<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class UnvisitedPlayableLikenessDisconnectedInteriorIslandAuditRegressionTest extends TestCase
{
    public function test_never_frontier_floor_like_islands_are_audited_without_granting_seed_authority(): void
    {
        $script = file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertIsString($script);

        self::assertStringContainsString('IV.30.1G.5Z.5 — Unvisited Playable-Likeness & Disconnected Interior Island Audit.', $script);
        self::assertStringContainsString('illustratedFrontierExaminedCells', $script);
        self::assertStringContainsString('illustratedFloorLikeNeverFrontierCells', $script);
        self::assertStringContainsString('illustratedNeverFrontierComponents', $script);
        self::assertStringContainsString('illustratedNeverFrontierExteriorComponents', $script);
        self::assertStringContainsString('illustratedNeverFrontierInteriorComponents', $script);
        self::assertStringContainsString('illustratedNeverFrontierNarrowGapComponents', $script);
        self::assertStringContainsString('illustratedDisconnectedPlayableIslandComponents', $script);
        self::assertStringContainsString('illustratedDisconnectedPlayableIslandCells', $script);
        self::assertStringContainsString('unique frontier-examined cells', $script);
        self::assertStringContainsString('floor-like never-frontier cells', $script);
        self::assertStringContainsString('disconnected playable islands', $script);
        self::assertStringContainsString('This phase deliberately grants no new seed/admission authority.', $script);
        self::assertStringNotContainsString('floor[row][column]=true; // G.5Z.5', $script);
    }
}
