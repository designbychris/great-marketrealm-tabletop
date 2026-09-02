<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Presentation;

use PHPUnit\Framework\TestCase;

final class AdventurersInMiniatureRegressionTest extends TestCase
{
    private function source(string $path): string
    {
        return (string) file_get_contents(dirname(__DIR__, 4) . '/' . $path);
    }

    public function test_turn_of_battle_uses_the_pixel_battle_status_language(): void
    {
        $css = $this->source('assets/css/tabletop.css');

        self::assertStringContainsString('IV.32.3 — Adventurers in Miniature', $css);
        self::assertStringContainsString('.gmrt-encounter-strip::before {', $css);
        self::assertStringContainsString('content: "BATTLE";', $css);
        self::assertStringContainsString('.gmrt-current-turn {', $css);
        self::assertStringContainsString('.gmrt-end-turn {', $css);
    }

    public function test_gathering_seats_can_show_the_current_character_turn(): void
    {
        $view = $this->source('app/Tabletop/Views/chamber.php');
        $js = $this->source('assets/js/tabletop.js');
        $css = $this->source('assets/css/tabletop.css');

        self::assertStringContainsString('data-party-character-id=', $view);
        self::assertStringContainsString("' is-active-turn'", $view);
        self::assertStringContainsString('function syncGatheringTurnState()', $js);
        self::assertStringContainsString("activeToken?.dataset.tokenSource", $js);
        self::assertStringContainsString('.gmrt-party__member.is-active-turn::after {', $css);
        self::assertStringContainsString('content: "TURN";', $css);
    }

    public function test_live_gathering_refresh_keeps_character_identity_for_turn_sync(): void
    {
        $js = $this->source('assets/js/tabletop.js');

        self::assertStringContainsString("const characterId = String(member.companion_character_id || '');", $js);
        self::assertStringContainsString('item.dataset.partyCharacterId = characterId;', $js);
        self::assertStringContainsString('syncGatheringTurnState();', $js);
    }

    public function test_tokens_and_bestiary_instances_share_an_active_turn_pixel_grammar(): void
    {
        $css = $this->source('assets/css/tabletop.css');

        self::assertStringContainsString('.gmrt-token.is-active-turn::before {', $css);
        self::assertStringContainsString('.gmrt-bestiary-instance.is-active-turn,', $css);
        self::assertStringContainsString('.gmrt-bestiary-instance__turn {', $css);
        self::assertStringContainsString('.gmrt-bestiary-drawer__toggle.has-active-turn::after {', $css);
    }

    public function test_phase_remains_presentation_only_and_respects_reduced_motion(): void
    {
        $phase = $this->source('docs/Roadmap/PHASE-IV.32.3.md');
        $css = $this->source('assets/css/tabletop.css');

        self::assertStringContainsString('presentation-only', $phase);
        self::assertStringContainsString('does not change initiative, turn authority, targeting, damage, HP, conditions, movement, Fog or persistence', $phase);
        self::assertStringContainsString('@media (prefers-reduced-motion: reduce)', $css);
    }
}
