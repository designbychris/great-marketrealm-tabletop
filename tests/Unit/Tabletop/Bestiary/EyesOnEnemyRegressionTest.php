<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Bestiary;

use PHPUnit\Framework\TestCase;

final class EyesOnEnemyRegressionTest extends TestCase
{
    private function root(string $path): string
    {
        return dirname(__DIR__, 4) . '/' . ltrim($path, '/');
    }

    public function test_turn_of_battle_becomes_status_and_keeper_progression_instead_of_action_home(): void
    {
        $view = file_get_contents($this->root('app/Tabletop/Views/chamber.php'));
        self::assertStringContainsString('data-combat-guidance', $view);
        self::assertStringContainsString("Use the Keeper\\'s Bestiary for creature actions; End Turn remains here.", $view);
        self::assertStringContainsString("Use the Adventurer\\'s Satchel when your turn arrives.", $view);
        self::assertStringContainsString('data-end-turn', $view);
        self::assertStringContainsString('data-target-range-status', $view);
    }

    public function test_one_shared_authoritative_combat_dock_owns_the_existing_deeds(): void
    {
        $view = file_get_contents($this->root('app/Tabletop/Views/chamber.php'));
        self::assertStringContainsString('data-combat-dock', $view);
        foreach (['attack', 'dash', 'disengage', 'dodge', 'help'] as $deed) {
            self::assertStringContainsString("'{$deed}'", $view);
        }
        self::assertStringContainsString('data-arsenal-attack', $view);
        self::assertStringContainsString('data-attack-target', $view);
    }

    public function test_player_turn_moves_the_combat_dock_into_the_satchel(): void
    {
        $view = file_get_contents($this->root('app/Tabletop/Views/chamber.php'));
        $js = file_get_contents($this->root('assets/js/tabletop.js'));
        self::assertStringContainsString('data-satchel-combat-mount', $view);
        self::assertStringContainsString("viewerRole === 'player'", $js);
        self::assertStringContainsString('controller === viewerUserId', $js);
        self::assertStringContainsString('mount = satchelCombatMount', $js);
    }

    public function test_keeper_turn_targets_the_exact_deployed_bestiary_instance_not_only_definition(): void
    {
        $view = file_get_contents($this->root('app/Tabletop/Views/chamber.php'));
        $js = file_get_contents($this->root('assets/js/tabletop.js'));
        self::assertStringContainsString('data-bestiary-instance-id=', $view);
        self::assertStringContainsString('data-bestiary-combat-mount', $view);
        self::assertStringContainsString("source.startsWith('gmrt-bestiary:')", $js);
        self::assertStringContainsString("[data-bestiary-instance-id=\"", $js);
    }

    public function test_active_bestiary_instance_card_and_tab_receive_turn_beacons(): void
    {
        $view = file_get_contents($this->root('app/Tabletop/Views/chamber.php'));
        $js = file_get_contents($this->root('assets/js/tabletop.js'));
        $css = file_get_contents($this->root('assets/css/tabletop.css'));
        self::assertStringContainsString('data-bestiary-turn-badge', $view);
        self::assertStringContainsString("card.classList.toggle('is-active-turn'", $js);
        self::assertStringContainsString("bestiaryToggle?.classList.add('has-active-turn')", $js);
        self::assertStringContainsString('.gmrt-bestiary-drawer__toggle.has-active-turn', $css);
    }

    public function test_clicking_a_viewer_safe_battlefield_token_can_choose_the_attack_target(): void
    {
        $js = file_get_contents($this->root('assets/js/tabletop.js'));
        self::assertStringContainsString('Array.from(attackTarget.options).some', $js);
        self::assertStringContainsString('attackTarget.value = tokenId', $js);
        self::assertStringContainsString('updateTargeting();', $js);
        self::assertStringContainsString('tokenId !== attackerId', $js);
    }

    public function test_live_turn_heartbeat_rehomes_and_repopulates_the_same_combat_dock(): void
    {
        $js = file_get_contents($this->root('assets/js/tabletop.js'));
        self::assertStringContainsString('function populateCombatDock(', $js);
        self::assertStringContainsString('function syncCombatDock(', $js);
        self::assertStringContainsString('state.arsenals || {}', $js);
        self::assertStringContainsString('state.tokens || []', $js);
        self::assertStringContainsString('syncCombatDock(state);', $js);

        $view = file_get_contents($this->root('app/Tabletop/Views/chamber.php'));
        self::assertLessThan(
            strpos($view, 'data-live-lifecycle'),
            strpos($view, 'data-combat-dock')
        );
    }

    public function test_eyes_on_enemy_is_recorded_before_the_keepers_menagerie_and_cartography(): void
    {
        $roadmap = file_get_contents($this->root('ROADMAP.md'));
        self::assertStringContainsString('[x] **IV.29C.1 — Eyes on the Enemy**', $roadmap);
        self::assertStringContainsString("[x] **IV.29D — The Keeper's Menagerie**", $roadmap);
        self::assertLessThan(
            strpos($roadmap, "IV.29D — The Keeper's Menagerie"),
            strpos($roadmap, 'IV.29C.1 — Eyes on the Enemy')
        );
        self::assertLessThan(
            strpos($roadmap, "Keeper's Cartography Assistant"),
            strpos($roadmap, "IV.29D — The Keeper's Menagerie")
        );

        $docs = file_get_contents($this->root('docs/Roadmap/PHASE-IV.29C.1.md'));
        self::assertStringContainsString("The Adventurer's Satchel owns Player actions", $docs);
        self::assertStringContainsString("the Keeper's Bestiary owns deployed creature actions", $docs);
    }
}
