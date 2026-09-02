<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Campaigns;

use PHPUnit\Framework\TestCase;

final class PippinRemembersWayRegressionTest extends TestCase
{
    private function root(): string { return dirname(__DIR__, 4); }

    public function test_gateway_discovers_owned_active_and_invited_tables_for_current_user(): void
    {
        $source = file_get_contents($this->root() . '/app/Tabletop/Presentation/TabletopShortcode.php');
        self::assertStringContainsString('$registry->all()', $source);
        self::assertStringContainsString('TableMemberStatus::ACTIVE', $source);
        self::assertStringContainsString('TableMemberStatus::INVITED', $source);
        self::assertStringContainsString("'is_owner' => \$isOwner", $source);
    }

    public function test_table_atlas_has_pippin_art_and_role_aware_return_routes(): void
    {
        $view = file_get_contents($this->root() . '/app/Tabletop/Views/chamber.php');
        self::assertStringContainsString("Pippin's Table Atlas", $view);
        self::assertStringContainsString('gmrt-wayfinder__scene', $view);
        self::assertStringContainsString('Return to Table', $view);
        self::assertStringContainsString('Take My Seat', $view);
    }

    public function test_only_keeper_cards_expose_player_management_and_permanent_removal(): void
    {
        $view = file_get_contents($this->root() . '/app/Tabletop/Views/chamber.php');
        self::assertStringContainsString('if ($isOwner)', $view);
        self::assertStringContainsString('data-campaign-invite-form', $view);
        self::assertStringContainsString('data-remove-tabletop', $view);
        self::assertStringContainsString('This cannot be undone', $view);
    }

    public function test_removal_endpoint_is_nonce_protected_owner_authorized_and_registered(): void
    {
        $controller = file_get_contents($this->root() . '/app/Tabletop/Http/RemoveTabletopAjaxController.php');
        $remover = file_get_contents($this->root() . '/app/Tabletop/Campaigns/WordPressTabletopRemover.php');
        $provider = file_get_contents($this->root() . '/app/Tabletop/TabletopServiceProvider.php');
        self::assertStringContainsString('check_ajax_referer(TabletopAjaxController::NONCE_ACTION', $controller);
        self::assertStringContainsString('dungeonMasterUserId() !== $userId', $remover);
        self::assertStringContainsString("'gmrt_tables'", $remover);
        self::assertStringContainsString("'wp_ajax_gmrt_remove_tabletop'", $provider);
    }
}
