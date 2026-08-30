<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Bestiary;

use PHPUnit\Framework\TestCase;

final class KeeperMenagerieRegressionTest extends TestCase
{
    private function root(string $path): string { return dirname(__DIR__, 4) . '/' . ltrim($path, '/'); }

    public function test_menagerie_has_source_agnostic_boundary(): void
    {
        $source = file_get_contents($this->root('app/Tabletop/Bestiary/Contracts/BestiarySource.php'));
        self::assertStringContainsString('interface BestiarySource', $source);
        self::assertStringContainsString('public function records(): array', $source);
    }

    public function test_companion_adapter_uses_filter_not_companion_repository_classes(): void
    {
        $source = file_get_contents($this->root('app/Integration/Companion/CompanionBestiarySource.php'));
        self::assertStringContainsString("gmrc_tabletop_bestiary_records", $source);
        self::assertStringNotContainsString('CanonicalBestiary', $source);
        self::assertStringNotContainsString('Modules\\DungeonMaster', $source);
    }

    public function test_composite_repository_keeps_training_fallback_and_external_sources(): void
    {
        $repo = file_get_contents($this->root('app/Tabletop/Bestiary/Repositories/MenagerieBestiaryRepository.php'));
        self::assertStringContainsString('$this->fallback->all()', $repo);
        self::assertStringContainsString('$source->records()', $repo);
        self::assertStringContainsString('$records[$creature->id()] = $creature', $repo);
    }

    public function test_external_records_require_battlefield_measures(): void
    {
        $mapper = file_get_contents($this->root('app/Tabletop/Bestiary/Services/ExternalBestiaryMapper.php'));
        self::assertStringContainsString('$ac < 1 || $hp < 1', $mapper);
        self::assertStringContainsString('return null;', $mapper);
    }

    public function test_both_chamber_and_deployment_use_same_menagerie_factory(): void
    {
        foreach (['app/Tabletop/Services/TabletopChamberFactory.php','app/Tabletop/Bestiary/Services/BestiaryDeploymentManagerFactory.php'] as $file) {
            self::assertStringContainsString('BestiaryRepositoryFactory::make()', file_get_contents($this->root($file)));
        }
    }

    public function test_keeper_drawer_names_the_menagerie_phase(): void
    {
        $view = file_get_contents($this->root('app/Tabletop/Views/chamber.php'));
        self::assertStringContainsString("Dungeon Master's Drawer · IV.29D", $view);
        self::assertStringContainsString('Keeper’s Menagerie', $view);
    }

    public function test_deployment_preserves_namespaced_menagerie_creature_ids(): void
    {
        $controller = file_get_contents($this->root('app/Tabletop/Http/BestiaryAjaxController.php'));
        self::assertStringNotContainsString('sanitize_key((string) ($_POST[\'creature_id\'] ?? \'\'))', $controller);
        self::assertSame(2, substr_count($controller, 'sanitize_text_field((string) ($_POST[\'creature_id\'] ?? \'\'))'));
    }

    public function test_roadmap_completes_bestiary_umbrella_before_cartography_assistant(): void
    {
        $roadmap = file_get_contents($this->root('ROADMAP.md'));
        self::assertStringContainsString("[x] **IV.29 — The Keeper's Bestiary**", $roadmap);
        self::assertStringContainsString("[x] **IV.29D — The Keeper's Menagerie**", $roadmap);
        self::assertStringContainsString("[x] **IV.30 — Keeper's Cartography Assistant**", $roadmap);
    }
}
