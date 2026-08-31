<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class ConnectedDungeonRegressionTest extends TestCase
{
    private function root(string $path): string { return dirname(__DIR__, 4) . '/' . $path; }

    public function test_hybrid_requests_connected_playable_floor(): void
    {
        $js = (string) file_get_contents($this->root('assets/js/tabletop.js'));
        self::assertStringContainsString('livingContourCandidates({ connectPlayableFloor: true })', $js);
        self::assertStringContainsString('IV.30.1D.1 — The Connected Dungeon', $js);
    }

    public function test_connectivity_heals_only_thin_low_confidence_seams(): void
    {
        $js = (string) file_get_contents($this->root('assets/js/tabletop.js'));
        self::assertStringContainsString('darkness[row][column] > .38', $js);
        self::assertStringContainsString('horizontalPortal !== verticalPortal', $js);
        self::assertStringContainsString('bridgeCandidates', $js);
    }

    public function test_floor_regions_are_real_connected_components(): void
    {
        $js = (string) file_get_contents($this->root('assets/js/tabletop.js'));
        self::assertStringContainsString('const componentSizes = []', $js);
        self::assertStringContainsString('component[ny][nx] = componentId', $js);
        self::assertStringContainsString('meaningfulFloor', $js);
    }

    public function test_standalone_living_contour_does_not_enable_connectivity_healing(): void
    {
        $js = (string) file_get_contents($this->root('assets/js/tabletop.js'));
        self::assertStringContainsString('if (options.connectPlayableFloor === true)', $js);
        self::assertStringContainsString('cartographySuggestions = livingContourCandidates()', $js);
    }

    public function test_phase_records_review_first_mixed_map_contract(): void
    {
        $roadmap = (string) file_get_contents($this->root('ROADMAP.md'));
        $phase = (string) file_get_contents($this->root('docs/Roadmap/PHASE-IV.30.1D.1.md'));
        self::assertStringContainsString('[x] **IV.30.1D.1 — The Connected Dungeon / Playable-Floor Connectivity & Region Graph**', $roadmap);
        self::assertStringContainsString('mixed cave-and-constructed dungeon', $phase);
        self::assertStringContainsString('Nothing is saved automatically', $phase);
        self::assertStringContainsString('misaligned-grid benchmark remains reserved for later grid-registration work', $phase);
    }
}
