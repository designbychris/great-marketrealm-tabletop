<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Presentation;

use PHPUnit\Framework\TestCase;

final class BattlefieldFinalInspectionRegressionTest extends TestCase
{
    private function source(string $path): string
    {
        return (string) file_get_contents(dirname(__DIR__, 4) . '/' . $path);
    }

    public function test_final_inspection_declares_one_explicit_battlefield_visual_stack(): void
    {
        $css = $this->source('assets/css/tabletop.css');

        self::assertStringContainsString("IV.32.4D — The Battlefield's Final Inspection", $css);
        self::assertStringContainsString('--gmrt-battlefield-z-map: 1;', $css);
        self::assertStringContainsString('--gmrt-battlefield-z-veil: 8;', $css);
        self::assertStringContainsString('--gmrt-battlefield-z-tokens: 10;', $css);
        self::assertStringContainsString('--gmrt-battlefield-z-targeting: 11;', $css);
    }

    public function test_targeting_feedback_finishes_above_the_veil_and_miniatures(): void
    {
        $css = $this->source('assets/css/tabletop.css');

        self::assertStringContainsString('.gmrt-fog-layer { z-index: var(--gmrt-battlefield-z-veil); }', $css);
        self::assertStringContainsString('.gmrt-board__tokens { z-index: var(--gmrt-battlefield-z-tokens); }', $css);
        self::assertStringContainsString('.gmrt-targeting-layer { z-index: var(--gmrt-battlefield-z-targeting); }', $css);
        self::assertStringContainsString('mix-blend-mode: normal;', $css);
    }

    public function test_keeper_thresholds_sit_between_veil_and_miniatures(): void
    {
        $css = $this->source('assets/css/tabletop.css');

        self::assertStringContainsString('--gmrt-battlefield-z-veil: 8;', $css);
        self::assertStringContainsString('--gmrt-battlefield-z-thresholds: 9;', $css);
        self::assertStringContainsString('--gmrt-battlefield-z-tokens: 10;', $css);
        self::assertStringContainsString('.gmrt-threshold-marker {', $css);
    }

    public function test_decorative_battlefield_overlays_remain_pointer_transparent(): void
    {
        $css = $this->source('assets/css/tabletop.css');

        self::assertStringContainsString('.gmrt-footstep-layer,', $css);
        self::assertStringContainsString('.gmrt-light-layer,', $css);
        self::assertStringContainsString('.gmrt-fog-layer,', $css);
        self::assertStringContainsString('.gmrt-targeting-layer,', $css);
        self::assertStringContainsString('pointer-events: none;', $css);
    }

    public function test_phase_documents_authority_boundary_and_reduced_motion(): void
    {
        $phase = $this->source('docs/Roadmap/PHASE-IV.32.4D.md');
        $css = $this->source('assets/css/tabletop.css');

        self::assertStringContainsString('presentation-only', $phase);
        self::assertStringContainsString('does not alter rules-grid geometry, token coordinates, movement validation, Fog cells', $phase);
        self::assertStringContainsString('@media (prefers-reduced-motion: reduce)', $css);
    }
}
