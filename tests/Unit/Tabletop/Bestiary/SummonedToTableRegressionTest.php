<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Bestiary;

use PHPUnit\Framework\TestCase;

final class SummonedToTableRegressionTest extends TestCase
{
    private function root(string $path): string
    {
        return dirname(__DIR__, 4) . '/' . ltrim($path, '/');
    }

    public function test_deployment_crosses_definition_instance_boundary_through_one_service(): void
    {
        $service = file_get_contents($this->root('app/Tabletop/Bestiary/Services/BestiaryDeploymentManager.php'));
        self::assertStringContainsString('final class BestiaryDeploymentManager', $service);
        self::assertStringContainsString('private BestiaryRepository $bestiary', $service);
        self::assertStringContainsString('private TableTokenManager $tokenManager', $service);
        self::assertStringContainsString("'gmrt-bestiary:' . \$creature->id()", $service);
        self::assertStringContainsString('TableTokenType::CREATURE', $service);
    }

    public function test_only_active_dungeon_master_may_deploy_bestiary_creatures(): void
    {
        $service = file_get_contents($this->root('app/Tabletop/Bestiary/Services/BestiaryDeploymentManager.php'));
        self::assertStringContainsString('TableMemberStatus::ACTIVE', $service);
        self::assertStringContainsString('TableMemberRole::DUNGEON_MASTER', $service);
        self::assertStringContainsString('Only the Dungeon Master may summon creatures from the Bestiary.', $service);
    }

    public function test_manual_and_monster_threshold_deployment_are_both_server_owned(): void
    {
        $service = file_get_contents($this->root('app/Tabletop/Bestiary/Services/BestiaryDeploymentManager.php'));
        self::assertStringContainsString('public function deployAtPoint(', $service);
        self::assertStringContainsString('public function deployAtMonsterThreshold(', $service);
        self::assertStringContainsString('ThresholdType::MONSTER', $service);
        self::assertStringContainsString('Place a Monster Deployment Threshold', $service);
    }

    public function test_group_summoning_is_bounded_and_spread_around_the_scene_grid(): void
    {
        $service = file_get_contents($this->root('app/Tabletop/Bestiary/Services/BestiaryDeploymentManager.php'));
        self::assertStringContainsString('$quantity < 1 || $quantity > 12', $service);
        self::assertStringContainsString('$scene->gridSize() / $scene->width()', $service);
        self::assertStringContainsString('$scene->gridSize() / $scene->height()', $service);
        self::assertStringContainsString('$ordinal === 1 ? $creature->name()', $service);
    }

    public function test_hidden_summons_reuse_existing_authoritative_token_visibility(): void
    {
        $service = file_get_contents($this->root('app/Tabletop/Bestiary/Services/BestiaryDeploymentManager.php'));
        self::assertStringContainsString('TableTokenVisibility::HIDDEN', $service);
        self::assertStringContainsString('TableTokenVisibility::VISIBLE', $service);
        $view = file_get_contents($this->root('app/Tabletop/Views/chamber.php'));
        self::assertStringContainsString('Hidden from Players', $view);
    }

    public function test_ajax_endpoints_are_authenticated_nonce_protected_and_registered(): void
    {
        $controller = file_get_contents($this->root('app/Tabletop/Http/BestiaryAjaxController.php'));
        self::assertStringContainsString('is_user_logged_in()', $controller);
        self::assertStringContainsString('check_ajax_referer(TabletopAjaxController::NONCE_ACTION', $controller);
        $provider = file_get_contents($this->root('app/Tabletop/TabletopServiceProvider.php'));
        self::assertStringContainsString('wp_ajax_gmrt_bestiary_deploy_at_point', $provider);
        self::assertStringContainsString('wp_ajax_gmrt_bestiary_deploy_at_threshold', $provider);
    }

    public function test_browser_can_arm_map_placement_or_summon_at_threshold_and_preserve_preparation_scene(): void
    {
        $js = file_get_contents($this->root('assets/js/tabletop.js'));
        self::assertStringContainsString("request('gmrt_bestiary_deploy_at_point'", $js);
        self::assertStringContainsString("request('gmrt_bestiary_deploy_at_threshold'", $js);
        self::assertStringContainsString('preparationSceneId || projectedSceneId', $js);
        self::assertStringContainsString('event.stopImmediatePropagation()', $js);
        self::assertStringContainsString('bestiaryPlacement', $js);
    }

    public function test_iv29b_roadmap_and_tab_corrective_are_present(): void
    {
        $roadmap = file_get_contents($this->root('ROADMAP.md'));
        self::assertStringContainsString('[x] **IV.29B — Summoned to the Table**', $roadmap);
        $docs = file_get_contents($this->root('docs/Roadmap/PHASE-IV.29B.md'));
        self::assertStringContainsString('Manual map-click placement', $docs);
        $css = file_get_contents($this->root('assets/css/tabletop.css'));
        self::assertStringContainsString('top:16.5rem', $css);
    }
}
