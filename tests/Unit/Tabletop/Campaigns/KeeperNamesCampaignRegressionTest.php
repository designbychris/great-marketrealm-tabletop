<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Campaigns;

use PHPUnit\Framework\TestCase;

final class KeeperNamesCampaignRegressionTest extends TestCase
{
    private function source(string $path): string
    {
        return (string) file_get_contents(dirname(__DIR__, 4) . '/' . $path);
    }

    public function test_table_metadata_keeps_optional_campaign_description_backwards_compatible(): void
    {
        $source = $this->source('app/Tables/Models/Table.php');
        self::assertStringContainsString('private string $description', $source);
        self::assertStringContainsString("\$record['description'] ?? ''", $source);
        self::assertStringContainsString("'description' => \$this->description", $source);
    }

    public function test_keeper_can_create_named_tabletop_with_blank_first_scene(): void
    {
        $source = $this->source('app/Tabletop/Campaigns/TabletopCreator.php');
        self::assertStringContainsString('$this->tables->prepare($dungeonMasterUserId, $name, $description)', $source);
        self::assertStringContainsString('$this->tables->activate($table->id())', $source);
        self::assertStringContainsString("'The First Blank Page'", $source);
        self::assertStringContainsString('createGenerated(', $source);
    }

    public function test_creation_endpoint_is_nonce_protected_and_dm_authorised(): void
    {
        $source = $this->source('app/Tabletop/Http/CreateTabletopAjaxController.php');
        self::assertStringContainsString("check_ajax_referer(TabletopAjaxController::NONCE_ACTION, 'nonce')", $source);
        self::assertStringContainsString('! $this->policy->mayCreate($userId)', $source);
        self::assertStringContainsString("'Only a Dungeon Master may create a Tabletop.'", $source);
        self::assertStringContainsString("'wp_ajax_gmrt_create_tabletop'", $this->source('app/Tabletop/TabletopServiceProvider.php'));
    }

    public function test_campaign_shelf_lists_owned_tables_and_preserves_testing_grounds(): void
    {
        $shortcode = $this->source('app/Tabletop/Presentation/TabletopShortcode.php');
        $view = $this->source('app/Tabletop/Views/chamber.php');
        self::assertStringContainsString('$registry->all()', $shortcode);
        self::assertStringContainsString("'is_owner' => \$isOwner", $shortcode);
        self::assertStringContainsString("The Keeper's Campaign Shelf", $view);
        self::assertStringContainsString('data-create-tabletop', $view);
        self::assertStringContainsString('data-prepare-test-table', $view);
    }

    public function test_creation_client_enters_the_new_table_without_reusing_testing_fixture(): void
    {
        $source = $this->source('assets/js/tabletop.js');
        self::assertStringContainsString("request('gmrt_create_tabletop'", $source);
        self::assertStringContainsString("url.searchParams.set('table', data.table_id)", $source);
        self::assertStringContainsString("request('gmrt_prepare_test_table'", $source);
    }
}
