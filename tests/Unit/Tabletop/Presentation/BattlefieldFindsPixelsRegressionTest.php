<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Presentation;

use PHPUnit\Framework\TestCase;

final class BattlefieldFindsPixelsRegressionTest extends TestCase
{
    private function source(string $path): string
    {
        return (string) file_get_contents(dirname(__DIR__, 4) . '/' . $path);
    }

    public function test_battlefield_grid_and_tokens_use_pixel_presentation_without_geometry_changes(): void
    {
        $css = $this->source('assets/css/tabletop.css');

        self::assertStringContainsString('IV.32.4A — The Battlefield Finds Its Pixels', $css);
        self::assertStringContainsString('.gmrt-board__grid {', $css);
        self::assertStringContainsString('var(--gmrt-grid-size)', $css);
        self::assertStringContainsString('var(--gmrt-grid-offset-x, 0px)', $css);
        self::assertStringContainsString('.gmrt-token {', $css);
        self::assertStringContainsString('image-rendering: pixelated;', $css);
    }

    public function test_selected_and_active_tokens_have_distinct_pixel_cursors(): void
    {
        $css = $this->source('assets/css/tabletop.css');

        self::assertStringContainsString('.gmrt-token.is-selected:not(.is-active-turn)::after,', $css);
        self::assertStringContainsString('.gmrt-token.is-active-turn::before {', $css);
        self::assertStringContainsString('filter: drop-shadow(2px 2px 0 #2b2110);', $css);
        self::assertStringContainsString('.gmrt-token:focus-visible,', $css);
        self::assertStringContainsString('grid-template-columns:repeat(auto-fit,minmax(6.5rem,1fr))', $css);
        self::assertStringContainsString('.gmrt-combat-dock__deeds .gmrt-deed{min-width:0;white-space:nowrap}', $css);
    }

    public function test_targeting_and_threshold_marks_share_the_pixel_battlefield_grammar(): void
    {
        $css = $this->source('assets/css/tabletop.css');
        $view = $this->source('app/Tabletop/Views/chamber.php');

        self::assertStringContainsString('.gmrt-target-line {', $css);
        self::assertStringContainsString('shape-rendering: crispEdges;', $css);
        self::assertStringContainsString('.gmrt-target-range {', $css);
        self::assertStringContainsString('.gmrt-threshold-marker {', $css);
        self::assertStringContainsString('data-target-line', $view);
        self::assertStringContainsString('data-threshold-marker=', $view);
    }

    public function test_door_and_footstep_visuals_reuse_existing_authoritative_layers(): void
    {
        $css = $this->source('assets/css/tabletop.css');
        $view = $this->source('app/Tabletop/Views/chamber.php');

        self::assertStringContainsString('.gmrt-vision-barrier.is-door {', $css);
        self::assertStringContainsString('.gmrt-vision-barrier.is-door.is-open {', $css);
        self::assertStringContainsString('.gmrt-footstep {', $css);
        self::assertStringContainsString('.gmrt-footstep.is-memory {', $css);
        self::assertStringContainsString('data-footstep-layer', $view);
    }

    public function test_certified_attack_result_reflows_inside_narrow_combat_docks(): void
    {
        $css = $this->source('assets/css/tabletop.css');

        self::assertStringContainsString('container-name: gmrt-combat-dock;', $css);
        self::assertStringContainsString('@container gmrt-combat-dock (max-width: 42rem)', $css);
        self::assertStringContainsString('.gmrt-diceworks__outcome,', $css);
        self::assertStringContainsString('grid-column: 1 / -1;', $css);
        self::assertStringContainsString('white-space: nowrap;', $css);
    }

    public function test_transient_attack_roll_is_cleared_when_authoritative_turn_identity_changes(): void
    {
        $js = $this->source('assets/js/tabletop.js');
        $view = $this->source('app/Tabletop/Views/chamber.php');

        self::assertStringContainsString('function clearTransientCombatRoll()', $js);
        self::assertStringContainsString('function currentEncounterTurnIdentity(encounterNode = null)', $js);
        self::assertStringContainsString('previousTurnIdentity !== incomingTurnIdentity', $js);
        self::assertStringContainsString('clearTransientCombatRoll();', $js);
        self::assertStringContainsString("diceworks.dataset.turnIdentity = currentEncounterTurnIdentity();", $js);
        self::assertStringContainsString('data-encounter-round=', $view);
    }

    public function test_phase_is_documented_as_presentation_only_and_reduced_motion_safe(): void
    {
        $phase = $this->source('docs/Roadmap/PHASE-IV.32.4A.md');
        $css = $this->source('assets/css/tabletop.css');

        self::assertStringContainsString('presentation-only', $phase);
        self::assertStringContainsString('does not alter rules-grid geometry, token coordinates, movement validation, targeting authority, Vision, Fog, encounters or persistence', $phase);
        self::assertStringContainsString('@media (prefers-reduced-motion: reduce)', $css);
    }
}
