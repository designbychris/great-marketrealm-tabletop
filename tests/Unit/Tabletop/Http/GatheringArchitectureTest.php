<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Http;

use PHPUnit\Framework\TestCase;

final class GatheringArchitectureTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        $this->root = dirname(__DIR__, 4);
    }

    public function testInvitationControllerRequiresDungeonMasterMembership(): void
    {
        $source = (string) file_get_contents(
            $this->root . '/app/Tabletop/Http/GatheringAjaxController.php'
        );

        self::assertStringContainsString('assertDungeonMaster', $source);
        self::assertStringContainsString('isDungeonMaster()', $source);
        self::assertStringContainsString('TableMemberStatus::ACTIVE', $source);
    }

    public function testInvitationActionsAreRegisteredAsAuthenticatedAjaxOnly(): void
    {
        $source = (string) file_get_contents(
            $this->root . '/app/Tabletop/TabletopServiceProvider.php'
        );

        self::assertStringContainsString('wp_ajax_gmrt_invite_table_player', $source);
        self::assertStringContainsString('wp_ajax_gmrt_accept_table_invitation', $source);
        self::assertStringNotContainsString('wp_ajax_nopriv_gmrt_invite_table_player', $source);
    }

    public function testChamberFactoryOwnsIdentityAndCompanionAdapters(): void
    {
        $source = (string) file_get_contents(
            $this->root . '/app/Tabletop/Services/TabletopChamberFactory.php'
        );

        self::assertStringContainsString('WordPressTableMemberIdentityDirectory', $source);
        self::assertStringContainsString('WordPressCompanionCharacterGateway', $source);
        self::assertStringNotContainsString('CompanionAvailability', $source);
    }

    public function testPhaseKeepsCompanionNamespaceInsideIntegrationBoundary(): void
    {
        $source = (string) file_get_contents(
            $this->root . '/app/Tabletop/Services/TabletopChamber.php'
        );

        self::assertStringNotContainsString('GreatMarketrealmCompanion\\', $source);
        self::assertStringContainsString('CompanionGateway', $source);
    }
    public function testPlayerRemovalIsAuthenticatedAndDungeonMasterControlled(): void
    {
        $provider = (string) file_get_contents($this->root . '/app/Tabletop/TabletopServiceProvider.php');
        $controller = (string) file_get_contents($this->root . '/app/Tabletop/Http/GatheringAjaxController.php');
        self::assertStringContainsString('wp_ajax_gmrt_remove_table_player', $provider);
        self::assertStringNotContainsString('wp_ajax_nopriv_gmrt_remove_table_player', $provider);
        self::assertStringContainsString('assertDungeonMaster($tableId)', $controller);
    }

}
