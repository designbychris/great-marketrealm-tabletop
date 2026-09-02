<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Campaigns;

use PHPUnit\Framework\TestCase;

final class KeeperOpensDoorsRegressionTest extends TestCase
{
    private function source(string $path): string
    {
        return (string) file_get_contents(dirname(__DIR__, 4) . '/' . $path);
    }

    public function test_campaign_shelf_projects_each_owned_tables_gathering(): void
    {
        $source = $this->source('app/Tabletop/Presentation/TabletopShortcode.php');
        self::assertStringContainsString('$this->members->forTable($table->id())', $source);
        self::assertStringContainsString('new TableMemberProjector($this->identities)', $source);
        self::assertStringContainsString("'roster' => \$roster", $source);
    }

    public function test_campaign_cards_expose_player_management_without_opening_table(): void
    {
        $view = $this->source('app/Tabletop/Views/chamber.php');
        self::assertStringContainsString('Manage Players', $view);
        self::assertStringContainsString('data-campaign-invite-form', $view);
        self::assertStringContainsString('data-campaign-remove-player', $view);
        self::assertStringContainsString('data-campaign-gathering-status', $view);
    }

    public function test_campaign_summons_reuse_authoritative_gathering_endpoint_with_explicit_table(): void
    {
        $source = $this->source('assets/js/tabletop.js');
        self::assertStringContainsString("request('gmrt_invite_table_player', { table_id: campaignTableId, player })", $source);
        self::assertStringContainsString("request('gmrt_remove_table_player', { table_id: campaignTableId, user_id: userId })", $source);
    }

    public function test_existing_gathering_controller_remains_the_authority_for_dm_seat_changes(): void
    {
        $source = $this->source('app/Tabletop/Http/GatheringAjaxController.php');
        self::assertStringContainsString('$this->assertDungeonMaster($tableId)', $source);
        self::assertStringContainsString('$this->gathering->invitePlayer($tableId, $userId)', $source);
        self::assertStringContainsString('$this->gathering->removePlayer($tableId, $userId)', $source);
    }
}
