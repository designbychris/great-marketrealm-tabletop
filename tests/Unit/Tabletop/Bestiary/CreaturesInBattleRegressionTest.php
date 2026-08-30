<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Bestiary;

use PHPUnit\Framework\TestCase;

final class CreaturesInBattleRegressionTest extends TestCase
{
    private function root(string $path): string
    {
        return dirname(__DIR__, 4) . '/' . ltrim($path, '/');
    }

    public function test_bestiary_combat_crosses_through_an_explicit_instance_provisioner(): void
    {
        $source = file_get_contents($this->root('app/Tabletop/Bestiary/Services/BestiaryCombatProvisioner.php'));
        self::assertStringContainsString('final class BestiaryCombatProvisioner', $source);
        self::assertStringContainsString('public function provision(', $source);
        self::assertStringContainsString('TableToken $token', $source);
        self::assertStringContainsString('BestiaryCreature $creature', $source);
        self::assertStringContainsString('instance snapshot', $source);
    }

    public function test_summoning_provisions_combat_immediately_after_token_forging(): void
    {
        $deployment = file_get_contents($this->root('app/Tabletop/Bestiary/Services/BestiaryDeploymentManager.php'));
        self::assertStringContainsString('private BestiaryCombatProvisioner $combatProvisioner', $deployment);
        self::assertStringContainsString('$this->combatProvisioner->provision(', $deployment);
        self::assertStringContainsString('$tableId,', $deployment);
        self::assertStringContainsString('$token,', $deployment);
        self::assertStringContainsString('$creature', $deployment);
    }

    public function test_bestiary_ac_and_hp_become_authoritative_instance_profiles(): void
    {
        $source = file_get_contents($this->root('app/Tabletop/Bestiary/Services/BestiaryCombatProvisioner.php'));
        self::assertStringContainsString('$creature->armorClass()', $source);
        self::assertStringContainsString('new Vitality(', $source);
        self::assertStringContainsString('$creature->hitPoints()', $source);
        self::assertStringContainsString('$this->combatProfiles->save(', $source);
        self::assertStringContainsString('$this->vitality->save(', $source);
    }

    public function test_every_bestiary_attack_becomes_an_existing_combat_arsenal_attack(): void
    {
        $source = file_get_contents($this->root('app/Tabletop/Bestiary/Services/BestiaryCombatProvisioner.php'));
        self::assertStringContainsString('foreach ($creature->attacks() as $record)', $source);
        self::assertStringContainsString('new ArsenalAttack(', $source);
        self::assertStringContainsString('AttackKind::assert(', $source);
        self::assertStringContainsString('new CombatArsenal(', $source);
        self::assertStringContainsString("'bestiary'", $source);
        self::assertStringContainsString("'gmrt-bestiary:' . \$creature->id() . ':' . \$id", $source);
    }

    public function test_damage_resistance_weakness_and_immunity_use_existing_defense_profile(): void
    {
        $source = file_get_contents($this->root('app/Tabletop/Bestiary/Services/BestiaryCombatProvisioner.php'));
        self::assertStringContainsString('new DamageDefenseProfile(', $source);
        self::assertStringContainsString('$creature->resistances()', $source);
        self::assertStringContainsString('$creature->weaknesses()', $source);
        self::assertStringContainsString('$creature->immunities()', $source);
    }

    public function test_factory_wires_all_existing_authoritative_combat_repositories(): void
    {
        $factory = file_get_contents($this->root('app/Tabletop/Bestiary/Services/BestiaryDeploymentManagerFactory.php'));
        foreach ([
            'WordPressCombatProfileRepository',
            'WordPressDamageProfileRepository',
            'WordPressCombatArsenalRepository',
            'WordPressDamageDefenseRepository',
            'WordPressVitalityRepository',
        ] as $repository) {
            self::assertStringContainsString('new ' . $repository . '()', $factory);
        }
    }

    public function test_existing_initiative_conditions_and_private_projection_reuse_authorized_scene_tokens(): void
    {
        $chamber = file_get_contents($this->root('app/Tabletop/Services/TabletopChamber.php'));
        self::assertStringContainsString('foreach ($tokens as $token)', $chamber);
        self::assertStringContainsString('$vitality[$tokenId] = $this->vitality', $chamber);
        self::assertStringContainsString('$conditions[$tokenId] = array_map(', $chamber);
        self::assertStringContainsString('$currentArsenal = $this->arsenals->forToken(', $chamber);
        self::assertStringContainsString('$arsenals[$tokenId] = $currentArsenal->toArray();', $chamber);

        $view = file_get_contents($this->root('app/Tabletop/Views/chamber.php'));
        self::assertStringContainsString('data-encounter-combatant', $view);
        self::assertStringContainsString('data-encounter-initiative=', $view);

        $encounters = file_get_contents($this->root('app/Tabletop/Encounters/Services/EncounterManager.php'));
        self::assertStringContainsString('Combatants must be tokens on the active Scene.', $encounters);

        $conditions = file_get_contents($this->root('app/Tabletop/Conditions/Services/ConditionManager.php'));
        self::assertStringContainsString('Conditions may only affect tokens on the Encounter Scene.', $conditions);
    }

    public function test_iv29c_is_recorded_and_the_keepers_menagerie_precedes_cartography(): void
    {
        $roadmap = file_get_contents($this->root('ROADMAP.md'));
        self::assertStringContainsString('[x] **IV.29C — Creatures in Battle**', $roadmap);
        self::assertStringContainsString("[ ] **IV.29D — The Keeper's Menagerie**", $roadmap);
        self::assertLessThan(
            strpos($roadmap, "Keeper's Cartography Assistant"),
            strpos($roadmap, "IV.29D — The Keeper's Menagerie")
        );
        $docs = file_get_contents($this->root('docs/Roadmap/PHASE-IV.29C.md'));
        self::assertStringContainsString('complete Tabletop combatant', $docs);
        self::assertStringContainsString("IV.29D — The Keeper's Menagerie", $docs);
    }
}
