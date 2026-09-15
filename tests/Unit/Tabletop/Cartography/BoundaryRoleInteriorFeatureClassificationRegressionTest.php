<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class BoundaryRoleInteriorFeatureClassificationRegressionTest extends TestCase
{
    private function root(string $relative): string
    {
        return dirname(__DIR__, 4) . '/' . ltrim($relative, '/');
    }

    public function test_living_contour_classifies_boundary_role_before_wall_authority(): void
    {
        $script = (string) file_get_contents($this->root('assets/js/tabletop.js'));
        self::assertStringContainsString('IV.30.1G.5E — Boundary Role & Interior Feature Classification', $script);
        self::assertStringContainsString("boundaryRole: 'interior-feature'", $script);
        self::assertStringContainsString("boundaryRole: 'terrain-elevation'", $script);
        self::assertStringContainsString("boundaryRole: 'playable-region-perimeter'", $script);
        self::assertStringContainsString("semanticBoundaryRole: entry.semanticBoundary?.boundaryRole || 'unresolved-evidence'", $script);
    }

    public function test_interior_features_are_not_automatic_los_walls(): void
    {
        $script = (string) file_get_contents($this->root('assets/js/tabletop.js'));
        self::assertStringContainsString("const semanticRoleIsAutomaticWall = (role) => role === 'structural-wall';", $script);
        self::assertStringContainsString("const interiorFeatureRole = ['interior-feature', 'terrain', 'obstacle', 'decoration-noise'].includes(semanticRole);", $script);
        self::assertStringContainsString('&& !interiorFeatureRole', $script);
        self::assertStringContainsString("'do-not-block-sight'", $script);
    }

    public function test_phase_is_documented(): void
    {
        $roadmap = (string) file_get_contents($this->root('ROADMAP.md'));
        $phase = (string) file_get_contents($this->root('docs/Roadmap/PHASE-IV.30.1G.5E.md'));
        self::assertStringContainsString('[x] **IV.30.1G.5E — Boundary Role & Interior Feature Classification**', $roadmap);
        self::assertStringContainsString('Pippin Learns What the Line Is For', $phase);
    }
}
