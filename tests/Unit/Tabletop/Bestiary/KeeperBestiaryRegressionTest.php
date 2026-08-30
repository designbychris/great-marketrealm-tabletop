<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Bestiary;

use PHPUnit\Framework\TestCase;

final class KeeperBestiaryRegressionTest extends TestCase
{
    private function root(string $path): string
    {
        return dirname(__DIR__, 4) . '/' . ltrim($path, '/');
    }

    public function test_bestiary_definition_is_separate_from_table_token_instances(): void
    {
        $creature = file_get_contents($this->root('app/Tabletop/Bestiary/Models/BestiaryCreature.php'));
        self::assertStringContainsString('final class BestiaryCreature', $creature);
        self::assertStringNotContainsString('use GreatMarketrealmTabletop\\Tables\\Tokens', $creature);
        self::assertStringNotContainsString('private TableToken', $creature);
        self::assertStringContainsString("'attacks' =>", $creature);
        self::assertStringContainsString("'ability_scores' =>", $creature);
        self::assertStringContainsString("'saving_throws' =>", $creature);
        self::assertStringContainsString("'senses' =>", $creature);
    }

    public function test_first_shelf_uses_the_combat_certified_training_creatures(): void
    {
        $repo = file_get_contents($this->root('app/Tabletop/Bestiary/Repositories/TrainingBestiaryRepository.php'));
        foreach (['Training Slime', 'Frosty Cheese Thing', 'Suspicious Training Dummy', 'Slime Slam', 'Toxic Spit', 'Chill Bite', 'Frost Shard', 'Wooden Fist', 'Ember Pop'] as $expected) {
            self::assertStringContainsString($expected, $repo);
        }
        self::assertStringContainsString("'slashing'", $repo);
        self::assertStringContainsString("'fire'", $repo);
        self::assertStringContainsString("'poison'", $repo);
    }

    public function test_bestiary_projection_is_dungeon_master_only(): void
    {
        $chamber = file_get_contents($this->root('app/Tabletop/Services/TabletopChamber.php'));
        self::assertStringContainsString('$viewer->isDungeonMaster() && $this->bestiary !== null', $chamber);
        self::assertStringContainsString('$this->bestiary->all()', $chamber);
        $state = file_get_contents($this->root('app/Tabletop/Models/TabletopChamberState.php'));
        self::assertStringContainsString('private array $bestiary = []', $state);
        self::assertStringContainsString('public function bestiary(): array', $state);
    }

    public function test_factory_wires_the_tabletop_owned_bestiary_repository(): void
    {
        $factory = file_get_contents($this->root('app/Tabletop/Services/TabletopChamberFactory.php'));
        self::assertStringContainsString('BestiaryRepositoryFactory::make()', $factory);
    }

    public function test_live_state_exposes_only_the_already_authorized_bestiary_projection(): void
    {
        $controller = file_get_contents($this->root('app/Tabletop/Http/TabletopAjaxController.php'));
        self::assertStringContainsString("'bestiary' => \$state->bestiary()", $controller);
    }

    public function test_keeper_gets_a_searchable_second_drawer_with_deployment_controls(): void
    {
        $view = file_get_contents($this->root('app/Tabletop/Views/chamber.php'));
        self::assertStringContainsString('data-keepers-bestiary', $view);
        self::assertStringContainsString("The Keeper's Bestiary", $view);
        self::assertStringContainsString('data-bestiary-search', $view);
        self::assertStringContainsString('data-bestiary-card', $view);
        self::assertStringContainsString('data-bestiary-place', $view);
        self::assertStringContainsString('data-bestiary-threshold', $view);
        self::assertStringContainsString('data-bestiary-quantity', $view);
        self::assertStringContainsString('data-bestiary-hidden', $view);
    }

    public function test_search_filters_the_rendered_catalogue_client_side(): void
    {
        $js = file_get_contents($this->root('assets/js/tabletop.js'));
        self::assertStringContainsString("document.querySelector('[data-bestiary-search]')", $js);
        self::assertStringContainsString("document.querySelectorAll('[data-bestiary-card]')", $js);
        self::assertStringContainsString('haystack.includes(query)', $js);
    }

    public function test_roadmap_preserves_the_agreed_bestiary_subphases(): void
    {
        $roadmap = file_get_contents($this->root('ROADMAP.md'));
        self::assertStringContainsString("IV.29A — The Keeper's Bestiary", $roadmap);
        self::assertStringContainsString('IV.29B — Summoned to the Table', $roadmap);
        self::assertStringContainsString('IV.29C — Creatures in Battle', $roadmap);
        self::assertStringContainsString("Keeper's Cartography Assistant", $roadmap);
    }
}
