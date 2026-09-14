<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class DoorwayThresholdReasoningRegressionTest extends TestCase
{
    private function root(string $relative): string
    {
        return dirname(__DIR__, 4) . '/' . ltrim($relative, '/');
    }

    public function test_threshold_reader_requires_supported_wall_runs_around_a_missing_span(): void
    {
        $script = (string) file_get_contents($this->root('assets/js/tabletop.js'));

        self::assertStringContainsString('IV.30.1G.2 — Doorway & Threshold Reasoning', $script);
        self::assertStringContainsString('const structuralByKey = new Map(noiseScreenedWalls.map((wall) =>', $script);
        self::assertStringContainsString('const considerThreshold = (x1, y1, x2, y2, before, after, sideA, sideB) =>', $script);
        self::assertStringContainsString('if (!before || !after || structuralEdge(x1, y1, x2, y2)) return;', $script);
        self::assertStringContainsString("evidence.push('architectural-support')", $script);
    }

    public function test_threshold_reader_requires_a_clear_opening_and_traversable_floor_on_both_sides(): void
    {
        $script = (string) file_get_contents($this->root('assets/js/tabletop.js'));

        self::assertStringContainsString('const openingContrast = opening.mean - averageWallTone', $script);
        self::assertStringContainsString('const clearOpening = opening.density <= .16 && openingContrast >= 12', $script);
        self::assertStringContainsString('const crossThresholdFloor = floorA.plausible && floorB.plausible', $script);
        self::assertStringContainsString('if (!clearOpening || !crossThresholdFloor || continuityStrength < 52) return;', $script);
        self::assertStringContainsString('thresholdEvidence: evidence', $script);
    }

    public function test_first_threshold_pass_is_conservative_and_explainable(): void
    {
        $script = (string) file_get_contents($this->root('assets/js/tabletop.js'));

        self::assertStringContainsString('widthGridUnits: 1', $script);
        self::assertStringContainsString("evidenceModel: 'local-contrast-topology-threshold-v3'", $script);
        self::assertStringContainsString("suggestion.doorwayReasoning ? 'Likely doorway' : 'Possible door'", $script);
        self::assertStringContainsString("suggestion.thresholdEvidence.join(' + ')", $script);
        self::assertStringContainsString('return noiseScreenedWalls.concat(doorwayCandidates)', $script);
    }

    public function test_phase_remains_review_first_and_is_recorded_in_the_roadmap(): void
    {
        $script = (string) file_get_contents($this->root('assets/js/tabletop.js'));
        $roadmap = (string) file_get_contents($this->root('ROADMAP.md'));
        $phase = (string) file_get_contents($this->root('docs/Roadmap/PHASE-IV.30.1G.2.md'));

        self::assertStringContainsString('renderCartographyReview()', $script);
        self::assertStringContainsString('.filter((item) => item.selected)', $script);
        self::assertStringContainsString('[x] **IV.30.1G.2 — Doorway & Threshold Reasoning**', $roadmap);
        self::assertStringContainsString('Doorway reasoning remains evidence, not authority', $phase);
        self::assertStringContainsString('never writes a door directly', $phase);
        self::assertStringContainsString('IV.30.1G.3', $phase);
    }
}
