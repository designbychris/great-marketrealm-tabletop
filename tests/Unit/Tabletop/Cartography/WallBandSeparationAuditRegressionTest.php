<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class WallBandSeparationAuditRegressionTest extends TestCase
{
    public function test_g5z47_profiles_fixed_normals_without_certifying_or_admitting_walls(): void
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertIsString($source);
        self::assertStringContainsString("IV.30.1G.5Z.47 — The Cartographer's Wall-Band Separation Audit", $source);
        self::assertStringContainsString('const wallBandSeparationAudit = (() => {', $source);
        self::assertStringContainsString('const distances = [-1.5, -1, -.75, -.5, -.25, 0, .25, .5, .75, 1, 1.5];', $source);
        self::assertStringContainsString('review.point.x + nx * contourStep * distanceCells', $source);
        self::assertStringContainsString('Boolean(completedSurfaceVisited[row]?.[column])', $source);
        self::assertStringContainsString('luminance(x, y) <= darkThreshold', $source);
        self::assertStringContainsString("'bounded-playable-ink-nonplayable-band-pattern-review-not-certified'", $source);
        self::assertStringContainsString('no-nearest-wall-search', $source);
        self::assertStringContainsString('wallBandSeparationAudit: wallBandSeparationAudit,', $source);
        self::assertStringContainsString('dataset.cartographyWallBand', $source);
        self::assertStringContainsString('G.5Z.47 wall-band separation unavailable', $source);
        self::assertStringContainsString('wallCertified: false, admittedEdges: 0, restoredRuns: 0', $source);
    }

    public function test_g5z47_preserves_certified_review_and_vertex_caps(): void
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertIsString($source);
        self::assertStringContainsString('const maximumReviewSuggestions = 200;', $source);
        self::assertStringContainsString('const maximumPathVertices = 256;', $source);
    }
}
