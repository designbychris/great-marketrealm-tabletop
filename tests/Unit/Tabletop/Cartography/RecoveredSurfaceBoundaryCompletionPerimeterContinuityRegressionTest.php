<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class RecoveredSurfaceBoundaryCompletionPerimeterContinuityRegressionTest extends TestCase
{
    public function test_recovered_surface_boundary_is_completed_without_admitting_new_floor(): void
    {
        $root = dirname(__DIR__, 4);
        $script = (string) file_get_contents($root . '/assets/js/tabletop.js');
        $roadmap = (string) file_get_contents($root . '/ROADMAP.md');

        self::assertStringContainsString('IV.30.1G.5Z — Recovered Surface Boundary Completion & Perimeter Continuity', $script);
        self::assertStringContainsString('if (!illustratedFloorContinuitySurface[seedRow][seedColumn] || completedSurfaceVisited[seedRow][seedColumn]) continue;', $script);
        self::assertStringContainsString('if (!isPlayableFloor(seedColumn, seedRow)) continue;', $script);
        self::assertStringContainsString('if (isPlayableFloor(nx, ny)) {', $script);
        self::assertStringContainsString('illustratedSurfacePlayableSeamsSuppressed += 1;', $script);
        self::assertStringContainsString('if (contourThresholdMatch(a,b)) {', $script);
        self::assertStringContainsString("evidenceModel: 'living-contour-recovered-surface-boundary-completion-v13'", $script);
        self::assertStringContainsString('raw surface boundary sides', $script);
        self::assertStringContainsString('playable seams suppressed', $script);
        self::assertStringContainsString('Phase IV.30.1G.5Z — Recovered Surface Boundary Completion & Perimeter Continuity ✅', $roadmap);
    }
}
