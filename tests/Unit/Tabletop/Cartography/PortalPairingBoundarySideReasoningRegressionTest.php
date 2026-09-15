<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class PortalPairingBoundarySideReasoningRegressionTest extends TestCase
{
    private function root(string $relative): string
    {
        return dirname(__DIR__, 4) . '/' . ltrim($relative, '/');
    }

    public function test_living_contour_pairs_boundary_ends_and_reasons_about_both_sides(): void
    {
        $script = (string) file_get_contents($this->root('assets/js/tabletop.js'));
        self::assertStringContainsString('IV.30.1G.5G — Portal Pairing & Boundary-Side Reasoning', $script);
        self::assertStringContainsString('const portalPairingAndBoundarySides = (entry) => {', $script);
        self::assertStringContainsString('.map(applyPortalPairingAndBoundarySides)', $script);
        self::assertStringContainsString('const boundarySideSeparation = sideAPlayable !== sideBPlayable;', $script);
    }

    public function test_certified_portals_stay_open_and_boundary_continuations_require_stronger_evidence(): void
    {
        $script = (string) file_get_contents($this->root('assets/js/tabletop.js'));
        self::assertStringContainsString("classification: 'certified-portal-pair'", $script);
        self::assertStringContainsString("'portal-remains-open'", $script);
        self::assertStringContainsString("classification: 'certified-boundary-continuation'", $script);
        self::assertStringContainsString("'safe-short-gap-recovery'", $script);
        self::assertStringContainsString("classification: 'unresolved-boundary-break'", $script);
        self::assertStringContainsString("'do-not-invent-portal-or-wall'", $script);
    }

    public function test_pairing_metadata_is_carried_into_review_suggestions(): void
    {
        $script = (string) file_get_contents($this->root('assets/js/tabletop.js'));
        self::assertStringContainsString("portalPairingClassification: entry.portalPairing?.classification || 'unresolved-boundary-break'", $script);
        self::assertStringContainsString('certifiedPortal: Boolean(entry.portalPairing?.portal)', $script);
        self::assertStringContainsString('certifiedBoundaryContinuation: Boolean(entry.portalPairing?.boundaryContinuation)', $script);
        self::assertStringContainsString('|| Boolean(entry.portalPairing?.portal)', $script);
    }

    public function test_phase_is_documented(): void
    {
        $roadmap = (string) file_get_contents($this->root('ROADMAP.md'));
        $phase = (string) file_get_contents($this->root('docs/Roadmap/PHASE-IV.30.1G.5G.md'));
        self::assertStringContainsString('[x] **IV.30.1G.5G — Portal Pairing & Boundary-Side Reasoning**', $roadmap);
        self::assertStringContainsString('Pippin Pairs the Jambs', $phase);
    }
}
