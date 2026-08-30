<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class KeepersCartographyAssistantRegressionTest extends TestCase
{
    private function root(string $relative): string
    {
        return dirname(__DIR__, 4) . '/' . ltrim($relative, '/');
    }

    public function test_assistant_is_presented_as_a_review_first_keeper_tool(): void
    {
        $view = (string) file_get_contents($this->root('app/Tabletop/Views/chamber.php'));

        self::assertStringContainsString("Keeper's Cartography Assistant", $view);
        self::assertStringContainsString('data-cartography-assistant-analyse', $view);
        self::assertStringContainsString('data-cartography-assistant-apply', $view);
        self::assertStringContainsString('Nothing is saved', (string) file_get_contents($this->root('docs/Roadmap/PHASE-IV.30.md')));
    }

    public function test_browser_analysis_uses_the_loaded_map_and_calibrated_square_grid(): void
    {
        $script = (string) file_get_contents($this->root('assets/js/tabletop.js'));

        self::assertStringContainsString("document.querySelector('[data-battlemap-image]')", $script);
        self::assertStringContainsString("(board.dataset.gridType || '') !== 'square'", $script);
        self::assertStringContainsString('getImageData', $script);
        self::assertStringContainsString('visionGrid()', $script);
    }

    public function test_draft_suggestions_have_a_separate_overlay_and_review_checklist(): void
    {
        $view = (string) file_get_contents($this->root('app/Tabletop/Views/chamber.php'));
        $script = (string) file_get_contents($this->root('assets/js/tabletop.js'));

        self::assertStringContainsString('data-cartography-suggestion-layer', $view);
        self::assertStringContainsString('data-cartography-assistant-review', $view);
        self::assertStringContainsString('gmrt-cartography-suggestion', $script);
        self::assertStringContainsString('cartographySuggestions[index].selected', $script);
    }

    public function test_only_selected_suggestions_are_submitted_for_authoritative_apply(): void
    {
        $script = (string) file_get_contents($this->root('assets/js/tabletop.js'));

        self::assertStringContainsString('.filter((item) => item.selected)', $script);
        self::assertStringContainsString("request('gmrt_apply_cartography_suggestions'", $script);
        self::assertStringContainsString('suggestions: JSON.stringify(selectedSuggestions)', $script);
    }

    public function test_server_batch_apply_is_dm_guarded_scene_scoped_and_bounded(): void
    {
        $manager = (string) file_get_contents($this->root('app/Tabletop/Vision/Services/VisionBarrierManager.php'));

        self::assertStringContainsString('public function addBatch(', $manager);
        self::assertStringContainsString('$scene=$this->guard($tableId,$userId,$sceneId);', $manager);
        self::assertStringContainsString('count($suggestions)>200', $manager);
        self::assertStringContainsString('$this->refreshExploration($tableId,$scene);', $manager);
    }

    public function test_assistant_ajax_uses_existing_nonce_and_scene_projection(): void
    {
        $controller = (string) file_get_contents($this->root('app/Tabletop/Http/CartographyAssistantAjaxController.php'));
        $provider = (string) file_get_contents($this->root('app/Tabletop/TabletopServiceProvider.php'));

        self::assertStringContainsString('check_ajax_referer(TabletopAjaxController::NONCE_ACTION', $controller);
        self::assertStringContainsString("\$_POST['scene_id']", $controller);
        self::assertStringContainsString('gmrt_apply_cartography_suggestions', $provider);
        self::assertStringContainsString("[\$this->cartographyAssistantAjax, 'apply']", $provider);
    }

    public function test_roadmap_marks_iv30_before_future_cartography_expansion(): void
    {
        $roadmap = (string) file_get_contents($this->root('ROADMAP.md'));

        self::assertStringContainsString("[x] **IV.30 — Keeper's Cartography Assistant**", $roadmap);
        self::assertStringContainsString('private review draft', $roadmap);
    }
}
