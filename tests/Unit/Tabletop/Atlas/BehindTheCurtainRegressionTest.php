<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Atlas;

use PHPUnit\Framework\TestCase;

final class BehindTheCurtainRegressionTest extends TestCase
{
    private function root(string $path): string { return dirname(__DIR__, 4) . '/' . $path; }

    public function test_keeper_atlas_is_a_dungeon_master_drawer(): void
    {
        $view = (string) file_get_contents($this->root('app/Tabletop/Views/chamber.php'));
        self::assertStringContainsString('gmrt-atlas-drawer', $view);
        self::assertStringContainsString('data-atlas-toggle', $view);
        self::assertStringContainsString('data-atlas-prepare-map', $view);
    }

    public function test_preparation_scene_is_private_and_dungeon_master_only(): void
    {
        $chamber = (string) file_get_contents($this->root('app/Tabletop/Services/TabletopChamber.php'));
        self::assertStringContainsString("Only the Dungeon Master may prepare a Scene behind the curtain.", $chamber);
        self::assertStringContainsString('$this->scenes->find($tableId, $preparationSceneId)', $chamber);
        self::assertStringContainsString("'live_scene_id' => \$liveScene?->id() ?? ''", $chamber);
    }

    public function test_live_state_and_fragment_accept_the_private_scene_projection(): void
    {
        $controller = (string) file_get_contents($this->root('app/Tabletop/Http/TabletopAjaxController.php'));
        self::assertGreaterThanOrEqual(2, substr_count($controller, '$this->sceneId()'));
        self::assertStringContainsString("'preparation' => \$state->preparation()", $controller);
    }

    public function test_cartography_fog_and_walls_are_bound_to_the_prepared_scene(): void
    {
        $cartography = (string) file_get_contents($this->root('app/Tabletop/Cartography/Services/CartographersTable.php'));
        $fog = (string) file_get_contents($this->root('app/Tabletop/Fog/Services/FogOfWarManager.php'));
        $vision = (string) file_get_contents($this->root('app/Tabletop/Vision/Services/VisionBarrierManager.php'));
        self::assertStringContainsString('$this->targetScene($tableId, $sceneId)', $cartography);
        self::assertStringContainsString('$this->targetScene($tableId, $sceneId)', $fog);
        self::assertStringContainsString('$this->guard($tableId,$userId,$sceneId)', $vision);
    }

    public function test_browser_carries_preparation_scene_id_on_authoritative_requests(): void
    {
        $js = (string) file_get_contents($this->root('assets/js/tabletop.js'));
        self::assertStringContainsString("const preparationSceneId = root.dataset.preparationSceneId || '';", $js);
        self::assertStringContainsString("body.set('scene_id', preparationSceneId);", $js);
        self::assertStringContainsString("Behind the Curtain — private Scene preparation.", $js);
        self::assertStringContainsString('data-exit-preparation', (string) file_get_contents($this->root('app/Tabletop/Views/chamber.php')));
    }
}
