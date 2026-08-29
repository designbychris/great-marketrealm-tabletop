<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Atlas;

use PHPUnit\Framework\TestCase;

final class PassageBetweenPlacesRegressionTest extends TestCase
{
    private function root(string $path): string
    {
        return dirname(__DIR__, 4) . '/' . $path;
    }

    public function test_chamber_exposes_the_projected_scene_identity(): void
    {
        $view = (string) file_get_contents($this->root('app/Tabletop/Views/chamber.php'));

        self::assertStringContainsString('data-scene-id=', $view);
        self::assertStringContainsString("(string) (\$scene['id'] ?? '')", $view);
    }

    public function test_living_table_detects_an_authoritative_scene_change(): void
    {
        $js = (string) file_get_contents($this->root('assets/js/tabletop.js'));

        self::assertStringContainsString("const projectedSceneId = root.dataset.sceneId || '';", $js);
        self::assertStringContainsString("const incomingSceneId = String(state.scene?.id || '');", $js);
        self::assertStringContainsString('incomingSceneId !== projectedSceneId', $js);
        self::assertStringContainsString('Passage Between Places — the Table carries you to a new Scene.', $js);
    }

    public function test_passage_rehydrates_the_chamber_without_a_page_reload(): void
    {
        $js = (string) file_get_contents($this->root('assets/js/tabletop.js'));

        self::assertStringContainsString("body.set('action', 'gmrt_tabletop_fragment');", $js);
        self::assertStringContainsString('current.replaceWith(incoming);', $js);
        self::assertStringContainsString('bootTabletop();', $js);
        self::assertStringNotContainsString('location.reload()', $js);
    }

    public function test_private_preparation_is_not_pulled_through_live_passage(): void
    {
        $js = (string) file_get_contents($this->root('assets/js/tabletop.js'));

        self::assertStringContainsString('!preparationSceneId', $js);
        self::assertStringContainsString("body.set('scene_id', preparationSceneId);", $js);
    }

    public function test_roadmap_records_passage_between_places_as_complete(): void
    {
        $roadmap = (string) file_get_contents($this->root('ROADMAP.md'));
        $phase = (string) file_get_contents($this->root('docs/Roadmap/PHASE-IV.28C.md'));

        self::assertStringContainsString('[x] **IV.28C — Passage Between Places**', $roadmap);
        self::assertStringContainsString('No second polling loop is introduced.', $phase);
        self::assertStringContainsString('Tokens remain Scene-owned.', $phase);
    }
}
