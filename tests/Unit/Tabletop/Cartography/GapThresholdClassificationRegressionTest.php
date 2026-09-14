<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class GapThresholdClassificationRegressionTest extends TestCase
{
    private function root(string $relative): string
    {
        return dirname(__DIR__, 4) . '/' . ltrim($relative, '/');
    }

    public function test_living_contour_reuses_structural_doorway_evidence_instead_of_inventing_a_second_authority(): void
    {
        $script = (string) file_get_contents($this->root('assets/js/tabletop.js'));

        self::assertStringContainsString('IV.30.1G.4B — Gap & Threshold Classification', $script);
        self::assertStringContainsString('const contourThresholdCandidates = Array.isArray(options.thresholdCandidates)', $script);
        self::assertStringContainsString("structuralCartographyCandidates().filter((item) => item?.type === 'door' && item?.doorwayReasoning)", $script);
        self::assertStringContainsString("'g2-structural-doorway'", $script);
    }

    public function test_certified_doorway_spans_split_living_contours_and_are_never_auto_bridged(): void
    {
        $script = (string) file_get_contents($this->root('assets/js/tabletop.js'));

        self::assertStringContainsString('const splitContourPathAtProtectedThresholds = (points) => {', $script);
        self::assertStringContainsString("classification: 'doorway-passage'", $script);
        self::assertStringContainsString("'protected-open-gap'", $script);
        self::assertStringContainsString("'do-not-auto-bridge'", $script);
        self::assertStringContainsString("partialContourRecovery: gapProtected", $script);
        self::assertStringContainsString("'protected-threshold-split'", $script);
    }

    public function test_unresolved_contour_ends_are_classified_for_keeper_review_without_becoming_walls(): void
    {
        $script = (string) file_get_contents($this->root('assets/js/tabletop.js'));

        self::assertStringContainsString("classification: 'map-edge'", $script);
        self::assertStringContainsString("classification: 'noise-gap'", $script);
        self::assertStringContainsString("classification: 'uncertain-boundary'", $script);
        self::assertStringContainsString('gapClassifications: endpointClassifications', $script);
        self::assertStringContainsString('protectedContourGaps: protectedPath.gaps', $script);
    }

    public function test_hybrid_passes_threshold_evidence_back_into_living_contour_and_phase_is_documented(): void
    {
        $script = (string) file_get_contents($this->root('assets/js/tabletop.js'));
        $roadmap = (string) file_get_contents($this->root('ROADMAP.md'));
        $phase = (string) file_get_contents($this->root('docs/Roadmap/PHASE-IV.30.1G.4B.md'));

        self::assertStringContainsString("const thresholdCandidates = structural.filter((item) => item?.type === 'door' && item?.doorwayReasoning);", $script);
        self::assertStringContainsString('livingContourCandidates({ connectPlayableFloor: true, thresholdCandidates })', $script);
        self::assertStringContainsString('livingContourCandidates({ thresholdCandidates })', $script);
        self::assertStringContainsString('doorway/passage gap', $script);
        self::assertStringContainsString('[x] **IV.30.1G.4B — Gap & Threshold Classification**', $roadmap);
        self::assertStringContainsString('An opening that Pippin can justify stays open.', $phase);
    }
}
