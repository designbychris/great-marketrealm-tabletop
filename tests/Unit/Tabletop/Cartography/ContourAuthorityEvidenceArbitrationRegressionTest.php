<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class ContourAuthorityEvidenceArbitrationRegressionTest extends TestCase
{
    public function test_direct_contours_have_authority_over_promoted_perimeter(): void
    {
        $root = dirname(__DIR__, 4);
        $script = (string) file_get_contents($root . '/assets/js/tabletop.js');
        $roadmap = (string) file_get_contents($root . '/ROADMAP.md');

        self::assertStringContainsString('IV.30.1G.5P — Contour Authority & Evidence Arbitration', $script);
        self::assertStringContainsString('authoritativeContourSuggestions', $script);
        self::assertStringContainsString('preOcclusionRecoveryContours.forEach(addAuthoritativeContour)', $script);
        self::assertStringContainsString('pathSuggestions.forEach(addAuthoritativeContour)', $script);
        self::assertStringContainsString("appendByAuthority(bridgeSupplementalSuggestions, 'bridge')", $script);
        self::assertStringContainsString("appendByAuthority(promotedSupplementalSuggestions, 'promoted')", $script);
        self::assertStringContainsString('authoritativePreserved', $script);
        self::assertStringContainsString('authoritative preserved', $script);
        self::assertStringContainsString('Phase IV.30.1G.5P — Contour Authority & Evidence Arbitration ✅', $roadmap);
    }
}
