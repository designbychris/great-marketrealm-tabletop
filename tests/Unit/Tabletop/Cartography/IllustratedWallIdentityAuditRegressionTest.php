<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class IllustratedWallIdentityAuditRegressionTest extends TestCase
{
    public function test_g5z46_combines_bounded_direction_and_playable_separation_without_wall_admission(): void
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertIsString($source);
        self::assertStringContainsString('IV.30.1G.5Z.46 — Illustrated Wall Identity Audit', $source);
        self::assertStringContainsString('const illustratedWallIdentityAudit = (() => {', $source);
        self::assertStringContainsString('negativeSide.playable !== positiveSide.playable', $source);
        self::assertStringContainsString('const directionallyAligned = alignedPairs > crossPairs && alignedPairs > 0;', $source);
        self::assertStringContainsString('const identityCandidate = floorSeparating && directionallyAligned && adaptiveInkCandidate;', $source);
        self::assertStringContainsString("'bounded-wall-identity-candidate-review-not-certified'", $source);
        self::assertStringContainsString('illustratedWallIdentityAudit: illustratedWallIdentityAudit,', $source);
        self::assertStringContainsString('data-audit-wall-identity', $source);
        self::assertStringContainsString('dataset.cartographyWallIdentity', $source);
        self::assertStringContainsString('G.5Z.46 illustrated wall identity unavailable', $source);
        self::assertStringContainsString('wallCertified: false, admittedEdges: 0, restoredRuns: 0', $source);
    }

    public function test_g5z46_preserves_review_and_vertex_caps(): void
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertIsString($source);
        self::assertStringContainsString('const maximumReviewSuggestions = 200;', $source);
        self::assertStringContainsString('const maximumPathVertices = 256;', $source);
    }
}
