<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class ContourChainCertificationRegressionTest extends TestCase
{
    private function root(string $relative): string
    {
        return dirname(__DIR__, 4) . '/' . ltrim($relative, '/');
    }

    public function test_hybrid_considers_both_living_readers_before_chain_arbitration(): void
    {
        $script = (string) file_get_contents($this->root('assets/js/tabletop.js'));
        self::assertStringContainsString('IV.30.1G.5D — Contour Chain Certification', $script);
        self::assertStringContainsString('const standaloneContours = livingContourCandidates({ thresholdCandidates });', $script);
        self::assertStringContainsString('const contours = connectedContours.concat(standaloneContours);', $script);
        self::assertStringContainsString("'certified-dual-living-readers'", $script);
    }

    public function test_whole_region_defining_chains_can_survive_locally_weak_spans(): void
    {
        $script = (string) file_get_contents($this->root('assets/js/tabletop.js'));
        self::assertStringContainsString('const contourChainCertification = (item, topologyEvidence) => {', $script);
        self::assertStringContainsString("regionMembership === 'connected-playable-boundary'", $script);
        self::assertStringContainsString('if (chainCertification?.certified) return false;', $script);
        self::assertStringContainsString("contourChainCertification: chainCertification.certified ? 'certified-region-boundary' : 'local-review-chain'", $script);
    }

    public function test_chain_certification_does_not_promote_tiny_interior_marks_or_seal_thresholds(): void
    {
        $script = (string) file_get_contents($this->root('assets/js/tabletop.js'));
        self::assertStringContainsString('const compactInterior = pathLength < 1.15 && points.length <= 5;', $script);
        self::assertStringContainsString('const thresholdInterrupted = Boolean(item.thresholdGapProtection)', $script);
        self::assertStringContainsString('if (thresholdNearSpan(a, b)) return false;', $script);
        self::assertStringContainsString('thresholdInterruptedChain: chainCertification.thresholdInterrupted', $script);
    }

    public function test_phase_is_documented(): void
    {
        $roadmap = (string) file_get_contents($this->root('ROADMAP.md'));
        $phase = (string) file_get_contents($this->root('docs/Roadmap/PHASE-IV.30.1G.5D.md'));
        self::assertStringContainsString('[x] **IV.30.1G.5D — Contour Chain Certification**', $roadmap);
        self::assertStringContainsString('The Boundary Earns Trust Together', $phase);
    }
}
