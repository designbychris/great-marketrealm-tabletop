<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class DungeonForgeRegressionTest extends TestCase
{
    private function source(string $path): string
    {
        return (string) file_get_contents(dirname(__DIR__, 4) . '/' . $path);
    }

    public function test_forge_is_keeper_review_first_before_build(): void
    {
        $view = $this->source('app/Tabletop/Views/chamber.php');
        $js = $this->source('assets/js/tabletop.js');

        self::assertStringContainsString("The Cartographer's Dungeon Forge", $view);
        self::assertStringContainsString('Nothing becomes authoritative until Build Dungeon', $view);
        self::assertStringContainsString('data-dungeon-forge-generate', $view);
        self::assertStringContainsString('data-dungeon-forge-build disabled', $view);
        self::assertStringContainsString('Forge draft cleared. Nothing was saved.', $js);
    }

    public function test_forge_geometry_is_deterministic_and_connected_before_derivation(): void
    {
        $js = $this->source('assets/js/tabletop.js');

        self::assertStringContainsString("const forgeRandom = (seed) =>", $js);
        self::assertStringContainsString("generateDungeonForgePlan = (seed, style)", $js);
        self::assertStringContainsString("const floor = new Map()", $js);
        self::assertStringContainsString("ordered.push(remaining.shift())", $js);
        self::assertStringContainsString("doors.push(forgeDoorAt", $js);
    }

    public function test_forge_derives_bounded_walls_doors_and_keeper_lights(): void
    {
        $js = $this->source('assets/js/tabletop.js');
        $controller = $this->source('app/Tabletop/Http/DungeonForgeAjaxController.php');

        self::assertStringContainsString("barriers.push({type:'wall'", $js);
        self::assertStringContainsString("barriers.push({type:'door'", $js);
        self::assertStringContainsString("barriers.length > 200", $js);
        self::assertStringContainsString("'brazier' => ['label' => 'Brazier', 'bright' => 60", $controller);
        self::assertStringContainsString("'magical' => ['label' => 'Magical Light', 'bright' => 40", $controller);
    }

    public function test_build_reuses_authoritative_grid_vision_fog_and_light_systems(): void
    {
        $js = $this->source('assets/js/tabletop.js');
        $controller = $this->source('app/Tabletop/Http/DungeonForgeAjaxController.php');

        self::assertStringContainsString("request('gmrt_calibrate_grid'", $js);
        self::assertStringContainsString("request('gmrt_build_dungeon_forge'", $js);
        self::assertStringContainsString('$this->vision->addBatch', $controller);
        self::assertStringContainsString('$this->lights->save($light)', $controller);
        self::assertStringContainsString('$this->fog->configure($tableId, $userId, true, true, $sceneId)', $controller);
    }

    public function test_forged_artwork_is_scene_persistent_and_projected_to_all_viewers(): void
    {
        $chamber = $this->source('app/Tabletop/Services/TabletopChamber.php');
        $view = $this->source('app/Tabletop/Views/chamber.php');
        $repo = $this->source('app/Tabletop/Cartography/Repositories/WordPressDungeonForgeRepository.php');

        self::assertStringContainsString("'dungeon_forge' =>", $chamber);
        self::assertStringContainsString('data-dungeon-forge-plan', $view);
        self::assertStringContainsString("gmrt_dungeon_forge_plans", $repo);
        self::assertStringContainsString('persistent Tabletop-native SVG', $this->source('docs/Roadmap/PHASE-IV.30.2.md'));
    }

    public function test_phase_is_wired_documented_and_versioned(): void
    {
        $provider = $this->source('app/Tabletop/TabletopServiceProvider.php');
        $plugin = $this->source('great-marketrealm-tabletop.php');
        $roadmap = $this->source('ROADMAP.md');
        $phase = $this->source('docs/Roadmap/PHASE-IV.30.2.md');

        self::assertStringContainsString("wp_ajax_gmrt_build_dungeon_forge", $provider);
        self::assertStringContainsString('0.32.0-alpha.5', $plugin);
        self::assertStringContainsString("IV.30.2 — The Cartographer's Dungeon Forge", $roadmap);
        self::assertStringContainsString('geometry first', $phase);
        self::assertStringContainsString('Behind the Curtain', $phase);
    }
}
