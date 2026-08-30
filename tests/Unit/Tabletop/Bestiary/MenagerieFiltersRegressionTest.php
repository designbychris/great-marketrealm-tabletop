<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Bestiary;

use PHPUnit\Framework\TestCase;

final class MenagerieFiltersRegressionTest extends TestCase
{
    private function root(string $path): string { return dirname(__DIR__, 4) . '/' . ltrim($path, '/'); }

    public function test_keeper_drawer_offers_scene_deployment_filters(): void
    {
        $view = file_get_contents($this->root('app/Tabletop/Views/chamber.php'));
        self::assertStringContainsString('data-bestiary-filter="all"', $view);
        self::assertStringContainsString('data-bestiary-filter="on-map"', $view);
        self::assertStringContainsString('data-bestiary-filter="not-on-map"', $view);
        self::assertStringContainsString('On This Map', $view);
        self::assertStringContainsString('Not On This Map', $view);
    }

    public function test_cards_are_marked_from_scene_owned_bestiary_instances(): void
    {
        $view = file_get_contents($this->root('app/Tabletop/Views/chamber.php'));
        self::assertStringContainsString('(string) ($token[\'source_reference\'] ?? \'\') === $creatureSource', $view);
        self::assertStringContainsString('data-bestiary-on-map=', $view);
        self::assertStringContainsString('data-bestiary-deployed-count=', $view);
        self::assertStringContainsString('ON MAP · ×', $view);
    }

    public function test_search_and_map_filter_are_applied_together(): void
    {
        $js = file_get_contents($this->root('assets/js/tabletop.js'));
        self::assertStringContainsString('function applyBestiaryFilters()', $js);
        self::assertStringContainsString('matchesSearch && matchesMap', $js);
        self::assertStringContainsString("bestiaryMapFilter === 'on-map' && onMap", $js);
        self::assertStringContainsString("bestiaryMapFilter === 'not-on-map' && !onMap", $js);
        self::assertStringContainsString("candidate.setAttribute('aria-pressed'", $js);
    }

    public function test_filter_counts_are_definition_based_and_phase_precedes_cartography(): void
    {
        $js = file_get_contents($this->root('assets/js/tabletop.js'));
        self::assertStringContainsString('function refreshBestiaryFilterCounts()', $js);
        self::assertStringContainsString("'on-map': onMap", $js);

        $roadmap = file_get_contents($this->root('ROADMAP.md'));
        $filter = strpos($roadmap, "IV.29D.1 — Menagerie Filters");
        $cartography = strpos($roadmap, "Keeper's Cartography Assistant");
        self::assertNotFalse($filter);
        self::assertNotFalse($cartography);
        self::assertLessThan($cartography, $filter);
    }
}
