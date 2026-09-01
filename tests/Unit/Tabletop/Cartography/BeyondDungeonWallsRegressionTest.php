<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class BeyondDungeonWallsRegressionTest extends TestCase
{
    private function source(string $path): string
    {
        return (string) file_get_contents(dirname(__DIR__, 4) . '/' . $path);
    }

    public function test_scene_forge_exposes_environment_separately_from_theme(): void
    {
        $view = $this->source('app/Tabletop/Views/chamber.php');
        $js = $this->source('assets/js/tabletop.js');

        self::assertStringContainsString('data-atlas-forge-scene-type', $view);
        self::assertStringContainsString('data-dungeon-forge-scene-type', $view);
        self::assertStringContainsString('<option value="dungeon" selected>Dungeon</option>', $view);
        self::assertStringContainsString('<option value="forest">Forest</option>', $view);
        self::assertStringContainsString('<option value="village">Village</option>', $view);
        self::assertStringContainsString('generateSceneForgePlan(sceneType, seed, style, theme', $js);
    }

    public function test_each_environment_has_its_own_topology_generator(): void
    {
        $js = $this->source('assets/js/tabletop.js');

        self::assertStringContainsString('const generateForestForgePlan =', $js);
        self::assertStringContainsString("scene_type:'forest'", $js);
        self::assertStringContainsString("kind:'clearing'", $js);
        self::assertStringContainsString("kind:'trail'", $js);
        self::assertStringContainsString("'tree-cluster'", $js);
        self::assertStringContainsString("'fallen-log'", $js);

        self::assertStringContainsString('const generateVillageForgePlan =', $js);
        self::assertStringContainsString("scene_type:'village'", $js);
        self::assertStringContainsString("kind:'road'", $js);
        self::assertStringContainsString("kind:'village-square'", $js);
        self::assertStringContainsString("'inn'", $js);
        self::assertStringContainsString("kind:'well'", $js);
        self::assertStringContainsString("kind:'fenced-garden'", $js);
    }

    public function test_theme_is_not_part_of_forest_or_village_topology_rng_seed(): void
    {
        $js = $this->source('assets/js/tabletop.js');

        self::assertStringContainsString('forgeRandom(`${seed}|${style}|forest`)', $js);
        self::assertStringContainsString('forgeRandom(`${seed}|${style}|village`)', $js);
        self::assertStringNotContainsString('forgeRandom(`${seed}|${style}|${theme}|forest`)', $js);
        self::assertStringNotContainsString('forgeRandom(`${seed}|${style}|${theme}|village`)', $js);
    }

    public function test_environment_features_feed_existing_authoritative_systems(): void
    {
        $js = $this->source('assets/js/tabletop.js');
        $controller = $this->source('app/Tabletop/Http/DungeonForgeAjaxController.php');

        self::assertStringContainsString("barriers.push({type:'wall'", $js);
        self::assertStringContainsString("barriers.push({type:'door'", $js);
        self::assertStringContainsString("'scene_type' => \$plan['scene_type']", $controller);
        self::assertStringContainsString("'features' => \$plan['features']", $controller);
        self::assertStringContainsString('$this->vision->addBatch', $controller);
        self::assertStringContainsString('$this->fog->configure', $controller);
        self::assertStringContainsString('$this->lights->save($light)', $controller);
    }

    public function test_scene_type_and_features_are_server_bounded_and_persisted(): void
    {
        $controller = $this->source('app/Tabletop/Http/DungeonForgeAjaxController.php');

        self::assertStringContainsString("['dungeon', 'forest', 'village']", $controller);
        self::assertStringContainsString("\$features = [];", $controller);
        self::assertStringContainsString('if (count($features) >= 80) break;', $controller);
        self::assertStringContainsString('if (count($floor) > 1600)', $controller);
    }

    public function test_scene_specific_surface_features_have_native_svg_treatment(): void
    {
        $js = $this->source('assets/js/tabletop.js');
        $css = $this->source('assets/css/tabletop.css');

        self::assertStringContainsString("class: 'gmrt-forge-features'", $js);
        self::assertStringContainsString('data-forge-scene-type="forest"', $css);
        self::assertStringContainsString('data-forge-scene-type="village"', $css);
        self::assertStringContainsString('.is-tree-cluster', $css);
        self::assertStringContainsString('.is-road', $css);
        self::assertStringContainsString('.is-inn', $css);
    }

    public function test_phase_is_documented(): void
    {
        $roadmap = $this->source('ROADMAP.md');
        $phase = $this->source('docs/Roadmap/PHASE-IV.30.2B.md');

        self::assertStringContainsString('IV.30.2B — Beyond the Dungeon Walls', $roadmap);
        self::assertStringContainsString('Environment / Scene Type chooses topology', $phase);
        self::assertStringContainsString('Theme chooses presentation', $phase);
        self::assertStringContainsString('Dungeon, Forest and Village', $phase);
    }
}
