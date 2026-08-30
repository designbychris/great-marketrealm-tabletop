<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Battle;

use PHPUnit\Framework\TestCase;

final class RollForDamageRegressionTest extends TestCase
{
    private function root(string $path): string
    {
        return dirname(__DIR__, 4) . '/' . ltrim($path, '/');
    }

    public function test_live_attack_ajax_stops_after_the_d20_and_returns_a_pending_damage_receipt(): void
    {
        $controller = file_get_contents($this->root('app/Tabletop/Http/AttackAjaxController.php'));
        self::assertStringContainsString("false\n            );", $controller);
        self::assertStringContainsString("'pending_damage' => \$result['pending_damage']", $controller);

        $manager = file_get_contents($this->root('app/Tabletop/Battle/Services/AttackManager.php'));
        self::assertStringContainsString('bool $autoResolveDamage = true', $manager);
        self::assertStringContainsString("'damage_profile' => \$damageProfile->toArray()", $manager);
        self::assertStringContainsString("'attack_event_id' => \$event->toArray()['id']", $manager);
    }

    public function test_damage_roll_uses_the_attack_event_as_a_single_use_server_receipt(): void
    {
        $source = file_get_contents($this->root('app/Tabletop/Battle/Services/DamageRollManager.php'));
        self::assertStringContainsString("(\$record['type'] ?? '') === 'damage-applied'", $source);
        self::assertStringContainsString("\$payload['attack_event_id']", $source);
        self::assertStringContainsString('Damage has already been rolled for that attack.', $source);
        self::assertStringContainsString("(\$attackRecord['type'] ?? '') !== 'attack-resolved'", $source);
    }

    public function test_browser_cannot_supply_damage_mechanics(): void
    {
        $controller = file_get_contents($this->root('app/Tabletop/Http/DamageRollAjaxController.php'));
        self::assertStringContainsString("\$_POST['attack_event_id']", $controller);
        self::assertStringNotContainsString("\$_POST['damage']", $controller);
        self::assertStringNotContainsString("\$_POST['modifier']", $controller);
        self::assertStringNotContainsString("\$_POST['damage_type']", $controller);
        self::assertStringNotContainsString("\$_POST['target_token_id']", $controller);
    }

    public function test_damage_reuses_existing_critical_defense_vitality_and_death_rules(): void
    {
        $source = file_get_contents($this->root('app/Tabletop/Battle/Services/DamageRollManager.php'));
        self::assertStringContainsString('AttackOutcome::CRITICAL_HIT', $source);
        self::assertStringContainsString('$this->damageResolver->resolve($profile, $critical)', $source);
        self::assertStringContainsString('$this->defenseResolver->resolve(', $source);
        self::assertStringContainsString('$vitality->damage($adjustment->resolvedDamage())', $source);
        self::assertStringContainsString('$deathSaveState->recordDamageFailure($failureCount)', $source);
        self::assertStringContainsString('$deathSaveState->markFallen()', $source);
    }

    public function test_guild_diceworks_exposes_damage_only_after_a_hit(): void
    {
        $view = file_get_contents($this->root('app/Tabletop/Views/chamber.php'));
        self::assertStringContainsString('data-roll-attack-damage', $view);

        $js = file_get_contents($this->root('assets/js/tabletop.js'));
        self::assertStringContainsString('function armDamageRoll(pendingDamage)', $js);
        self::assertStringContainsString("request('gmrt_roll_attack_damage'", $js);
        self::assertStringContainsString("if (data.pending_damage)", $js);
        self::assertStringContainsString('Hit certified — roll the authoritative damage dice.', $js);
    }

    public function test_phase_is_documented_before_the_menagerie(): void
    {
        $roadmap = file_get_contents($this->root('ROADMAP.md'));
        self::assertStringContainsString('[x] **IV.29C.1A — Roll for Damage**', $roadmap);
        self::assertLessThan(
            strpos($roadmap, "IV.29D — The Keeper's Menagerie"),
            strpos($roadmap, 'IV.29C.1A — Roll for Damage')
        );

        $docs = file_get_contents($this->root('docs/Roadmap/PHASE-IV.29C.1A.md'));
        self::assertStringContainsString('single-use pending-damage receipt', $docs);
        self::assertStringContainsString("IV.29D — The Keeper's Menagerie", $docs);
    }
}
