<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class PlayableRegionClosurePerimeterInferenceRegressionTest extends TestCase
{
    private function root(string $relative): string
    {
        return dirname(__DIR__, 4) . '/' . ltrim($relative, '/');
    }

    public function test_living_contour_can_reason_outward_from_certified_playable_space(): void
    {
        $script = (string) file_get_contents($this->root('assets/js/tabletop.js'));
        self::assertStringContainsString('IV.30.1G.5I — Playable Region Closure & Perimeter Inference', $script);
        self::assertStringContainsString('const playableRegionClosureAndPerimeterInference = () => {', $script);
        self::assertStringContainsString('const inferredPlayablePerimeterSuggestions = playableRegionClosureAndPerimeterInference();', $script);
        self::assertStringContainsString("partialContourRecovery: 'playable-region-perimeter-inference'", $script);
    }

    public function test_region_closure_is_local_and_cannot_absorb_exterior_whitespace(): void
    {
        $script = (string) file_get_contents($this->root('assets/js/tabletop.js'));
        self::assertStringContainsString('for (let pass = 0; pass < 2; pass += 1)', $script);
        self::assertStringContainsString('if (surroundingPlayable < 4 || orthogonalPlayable < 2) continue;', $script);
        self::assertStringContainsString('if (rawComponent >= 0 && exteriorFloorComponents.has(rawComponent)) continue;', $script);
        self::assertStringContainsString('if (darkness[row][column] > .58) continue;', $script);
    }

    public function test_inferred_perimeter_requires_wall_ink_and_preserves_thresholds(): void
    {
        $script = (string) file_get_contents($this->root('assets/js/tabletop.js'));
        self::assertStringContainsString('const threshold = contourThresholdMatch(a, b);', $script);
        self::assertStringContainsString('if (threshold) return;', $script);
        self::assertStringContainsString('if (localInk < .20) return;', $script);
        self::assertStringContainsString("'corroborating-wall-body-ink'", $script);
        self::assertStringContainsString("evidenceModel: 'living-contour-playable-region-closure-v8'", $script);
    }

    public function test_phase_is_documented(): void
    {
        $roadmap = (string) file_get_contents($this->root('ROADMAP.md'));
        $phase = (string) file_get_contents($this->root('docs/Roadmap/PHASE-IV.30.1G.5I.md'));
        self::assertStringContainsString('[x] **IV.30.1G.5I — Playable Region Closure & Perimeter Inference**', $roadmap);
        self::assertStringContainsString('Pippin Maps the Floor Outward', $phase);
    }
}
