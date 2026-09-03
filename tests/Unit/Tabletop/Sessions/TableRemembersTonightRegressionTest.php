<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Sessions;

use PHPUnit\Framework\TestCase;

final class TableRemembersTonightRegressionTest extends TestCase
{
    private function source(string $path): string
    {
        return (string) file_get_contents(dirname(__DIR__, 4) . '/' . $path);
    }

    public function test_table_atlas_can_link_keeper_table_to_companion_campaign(): void
    {
        $view = $this->source('app/Tabletop/Views/chamber.php');
        $script = $this->source('assets/js/tabletop.js');
        $provider = $this->source('app/Tabletop/TabletopServiceProvider.php');

        self::assertStringContainsString('data-companion-campaign-link', $view);
        self::assertStringContainsString('Companion Campaign', $view);
        self::assertStringContainsString("request('gmrt_link_companion_campaign'", $script);
        self::assertStringContainsString("'wp_ajax_gmrt_link_companion_campaign'", $provider);
    }

    public function test_companion_bridge_uses_deliberate_wordpress_contracts_not_companion_classes(): void
    {
        $bridge = $this->source('app/Integration/Companion/CompanionCampaignBridge.php');

        self::assertStringContainsString("'gmrt_companion_campaign_choices'", $bridge);
        self::assertStringContainsString("'gmrt_companion_campaign_for_table'", $bridge);
        self::assertStringContainsString("'gmrt_companion_link_campaign'", $bridge);
        self::assertStringContainsString("'gmrt_companion_sync_table_session'", $bridge);
        self::assertStringNotContainsString('GreatMarketrealmCompanion\\', $bridge);
    }

    public function test_linking_backfills_existing_table_sessions_into_companion(): void
    {
        $controller = $this->source('app/Tabletop/Http/CompanionCampaignAjaxController.php');

        self::assertStringContainsString('$this->sessions->forTable($tableId)', $controller);
        self::assertStringContainsString('$this->companion->synchronise($session, $userId)', $controller);
        self::assertStringContainsString("'sessions_synchronised'", $controller);
        self::assertStringContainsString('dungeonMasterUserId() !== $userId', $controller);
    }

    public function test_start_and_end_session_synchronise_the_authoritative_record(): void
    {
        $controller = $this->source('app/Tabletop/Http/TableSessionAjaxController.php');

        self::assertStringContainsString('CompanionCampaignBridge', $controller);
        self::assertStringContainsString('$this->companion->synchronise($session, get_current_user_id())', $controller);
        self::assertStringContainsString("'companion' =>", $controller);
    }
}
