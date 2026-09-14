<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class AdaptiveEvidenceModelRegressionTest extends TestCase
{
    private function root(string $relative): string
    {
        return dirname(__DIR__, 4) . '/' . ltrim($relative, '/');
    }

    public function test_imported_map_analysis_builds_local_luminance_evidence_once(): void
    {
        $script = (string) file_get_contents($this->root('assets/js/tabletop.js'));

        self::assertStringContainsString('IV.30.1G — Adaptive Evidence Model', $script);
        self::assertStringContainsString('const luminanceIntegral = new Float64Array', $script);
        self::assertStringContainsString('const luminanceSquaredIntegral = new Float64Array', $script);
        self::assertStringContainsString('const luminanceRegionStats = (x1, y1, x2, y2) =>', $script);
    }

    public function test_structural_reader_uses_relative_ink_contrast_before_absolute_darkness(): void
    {
        $script = (string) file_get_contents($this->root('assets/js/tabletop.js'));

        self::assertStringContainsString('const adaptiveInkEvidence = (x1, y1, x2, y2, neighbourhoodRadius) =>', $script);
        self::assertStringContainsString('const localContrast = neighbourhood.mean - sample.mean', $script);
        self::assertStringContainsString('const requiredContrast = Math.max(9, Math.min(32, 8 + (neighbourhood.deviation * .38)))', $script);
        self::assertStringContainsString('if (evidence.relativeInk || absoluteDarkInk)', $script);
        self::assertStringContainsString('const darkThreshold = 92', $script);
    }

    public function test_adaptive_evidence_does_not_remove_existing_hatch_rejection(): void
    {
        $script = (string) file_get_contents($this->root('assets/js/tabletop.js'));

        self::assertStringContainsString('const quietSide = Math.min(sideA, sideB)', $script);
        self::assertStringContainsString('const densityStructure = center >= .18 && quietSide <= .16 && center >= loudSide * 1.25', $script);
        self::assertStringContainsString('const adaptiveStructure = adaptiveEvidence.strong', $script);
        self::assertStringContainsString("evidenceModel: 'local-contrast-v1'", $script);
    }

    public function test_phase_remains_review_first_and_is_recorded_in_the_roadmap(): void
    {
        $script = (string) file_get_contents($this->root('assets/js/tabletop.js'));
        $roadmap = (string) file_get_contents($this->root('ROADMAP.md'));
        $phase = (string) file_get_contents($this->root('docs/Roadmap/PHASE-IV.30.1G.md'));

        self::assertStringContainsString('renderCartographyReview()', $script);
        self::assertStringContainsString('.filter((item) => item.selected)', $script);
        self::assertStringContainsString('[x] **IV.30.1G — The Cartographer Looks Again / Adaptive Evidence Model**', $roadmap);
        self::assertStringContainsString('local contrast is evidence, not authority', $phase);
        self::assertStringContainsString('Nothing is saved automatically', $phase);
    }
}
