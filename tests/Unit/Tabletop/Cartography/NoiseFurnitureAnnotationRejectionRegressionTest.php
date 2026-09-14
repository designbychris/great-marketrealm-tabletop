<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class NoiseFurnitureAnnotationRejectionRegressionTest extends TestCase
{
    private function root(string $relative): string
    {
        return dirname(__DIR__, 4) . '/' . ltrim($relative, '/');
    }

    public function test_structural_reader_builds_a_coarse_component_map_for_non_architectural_marks(): void
    {
        $script = (string) file_get_contents($this->root('assets/js/tabletop.js'));

        self::assertStringContainsString('IV.30.1G.3 — Noise, Furniture & Annotation Rejection', $script);
        self::assertStringContainsString('const noiseOccupied = new Uint8Array', $script);
        self::assertStringContainsString('const noiseLabels = new Int32Array', $script);
        self::assertStringContainsString('const noiseComponents = new Map()', $script);
        self::assertStringContainsString('longestGridSpan', $script);
        self::assertStringContainsString('fillRatio', $script);
    }

    public function test_busy_isotropic_texture_and_compact_marks_are_demoted_conservatively(): void
    {
        $script = (string) file_get_contents($this->root('assets/js/tabletop.js'));

        self::assertStringContainsString('const localClutterProfile = (x, y) =>', $script);
        self::assertStringContainsString('isotropic: minimum >= .11 && mean >= .20', $script);
        self::assertStringContainsString("evidence.push('compact-annotation')", $script);
        self::assertStringContainsString("evidence.push('furniture-like-outline')", $script);
        self::assertStringContainsString("evidence.push('busy-texture')", $script);
        self::assertStringContainsString('const rejected = isolated && compactMark && textureCluster', $script);
    }

    public function test_connected_architecture_defends_itself_and_thresholds_consume_the_screened_wall_set(): void
    {
        $script = (string) file_get_contents($this->root('assets/js/tabletop.js'));

        self::assertStringContainsString("evidence.push('topology-defends-wall')", $script);
        self::assertStringContainsString('penalty = Math.max(0, penalty - 7)', $script);
        self::assertStringContainsString('const noiseScreenedWalls = architecturalWalls.map', $script);
        self::assertStringContainsString('const structuralByKey = new Map(noiseScreenedWalls.map', $script);
        self::assertStringContainsString('return noiseScreenedWalls.concat(doorwayCandidates)', $script);
        self::assertStringContainsString("evidenceModel: 'local-contrast-topology-noise-v4'", $script);
    }

    public function test_noise_reasons_remain_visible_to_the_keeper_and_phase_is_documented(): void
    {
        $script = (string) file_get_contents($this->root('assets/js/tabletop.js'));
        $roadmap = (string) file_get_contents($this->root('ROADMAP.md'));
        $phase = (string) file_get_contents($this->root('docs/Roadmap/PHASE-IV.30.1G.3.md'));

        self::assertStringContainsString('suggestion.noiseEvidence', $script);
        self::assertStringContainsString('screened:', $script);
        self::assertStringContainsString('[x] **IV.30.1G.3 — Noise, Furniture & Annotation Rejection**', $roadmap);
        self::assertStringContainsString('Texture evidence by itself can never delete a wall.', $phase);
        self::assertStringContainsString('IV.30.1G.4 — Multi-Resolution Forensic Rescan', $phase);
    }
}
