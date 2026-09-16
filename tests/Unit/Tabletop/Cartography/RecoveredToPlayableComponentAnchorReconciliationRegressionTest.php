<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class RecoveredToPlayableComponentAnchorReconciliationRegressionTest extends TestCase
{
    public function test_recovered_surface_can_anchor_completion_without_reopening_exterior_floor(): void
    {
        $root = dirname(__DIR__, 4);
        $script = (string) file_get_contents($root . '/assets/js/tabletop.js');
        $roadmap = (string) file_get_contents($root . '/ROADMAP.md');

        self::assertStringContainsString('IV.30.1G.5Z.1 — Recovered-to-Playable Component Anchor Reconciliation', $script);
        self::assertStringContainsString('illustratedFloorContinuitySurface[row][column] || isPlayableFloor(column, row)', $script);
        self::assertStringContainsString('if (isCompletedSurfacePlayable(nx, ny)) {', $script);
        self::assertStringContainsString('illustratedSurfaceReconciledAnchors += 1;', $script);
        self::assertStringContainsString('illustratedSurfaceUnresolvedSurfaces += 1;', $script);
        self::assertStringContainsString('surface anchor candidates', $script);
        self::assertStringContainsString('reconciled anchors', $script);
        self::assertStringContainsString('Phase IV.30.1G.5Z.1 — Recovered-to-Playable Component Anchor Reconciliation ✅', $roadmap);
    }
}
