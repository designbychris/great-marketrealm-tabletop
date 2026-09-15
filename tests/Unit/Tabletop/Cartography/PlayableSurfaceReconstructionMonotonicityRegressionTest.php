<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class PlayableSurfaceReconstructionMonotonicityRegressionTest extends TestCase
{
    private function tabletop(): string
    {
        return (string) file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
    }

    public function test_occlusion_recovery_keeps_a_pre_recovery_living_contour_baseline(): void
    {
        $source = $this->tabletop();

        self::assertStringContainsString('const preOcclusionRecoveryContours = options.skipOcclusionRecovery === true', $source);
        self::assertStringContainsString('livingContourCandidates({ ...options, skipOcclusionRecovery: true })', $source);
    }

    public function test_no_recoverable_chain_falls_back_to_the_pre_recovery_certification(): void
    {
        $source = $this->tabletop();

        self::assertStringContainsString('return preOcclusionRecoveryContours;', $source);
        self::assertStringContainsString('if (options.skipOcclusionRecovery !== true)', $source);
    }

    public function test_g5j_merges_new_evidence_without_discarding_existing_candidates(): void
    {
        $source = $this->tabletop();

        self::assertStringContainsString('preOcclusionRecoveryContours.forEach((item) => merged.set(cartographySuggestionKey(item), item));', $source);
        self::assertStringContainsString('if (merged.size >= maximumReviewSuggestions) return;', $source);
        self::assertStringContainsString('if (!merged.has(key)) merged.set(key, item);', $source);
    }
}
