<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class AuthoritativePathCoalescenceReviewCapacityLiberationRegressionTest extends TestCase
{
    public function test_authoritative_paths_can_share_review_objects_without_losing_source_geometry(): void
    {
        $root = dirname(__DIR__, 4);
        $script = (string) file_get_contents($root . '/assets/js/tabletop.js');
        $roadmap = (string) file_get_contents($root . '/ROADMAP.md');

        self::assertStringContainsString('IV.30.1G.5X — Authoritative Path Coalescence & Review Capacity Liberation', $script);
        self::assertStringContainsString('members.length !== 2', $script);
        self::assertStringContainsString('authoritativeCompatibilityKey(left) !== authoritativeCompatibilityKey(right)', $script);
        self::assertStringContainsString('exact-endpoint-no-gap', $script);
        self::assertStringContainsString('source-geometry-preserved', $script);
        self::assertStringContainsString('authoritativeSourcesPreserved', $script);
        self::assertStringContainsString('authoritativeReviewSlotsLiberated', $script);
        self::assertStringContainsString("appendByAuthority(remainingReconstructedSurfaceSuggestions, 'surface');", $script);
        self::assertStringContainsString('authority merges', $script);
        self::assertStringContainsString('review slots liberated', $script);
        self::assertStringContainsString('Phase IV.30.1G.5X — Authoritative Path Coalescence & Review Capacity Liberation ✅', $roadmap);
    }
}
