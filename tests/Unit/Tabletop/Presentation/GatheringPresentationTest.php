<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Presentation;

use PHPUnit\Framework\TestCase;

final class GatheringPresentationTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        $this->root = dirname(__DIR__, 4);
    }

    public function testGatheringRendersProjectedIdentityInsteadOfUserNumber(): void
    {
        $view = (string) file_get_contents(
            $this->root . '/app/Tabletop/Views/chamber.php'
        );

        self::assertStringContainsString("\$member['display_name']", $view);
        self::assertStringContainsString("\$member['avatar_url']", $view);
        self::assertStringNotContainsString('User #<?php echo', $view);
    }

    public function testDungeonMasterHasInvitationControl(): void
    {
        $view = (string) file_get_contents(
            $this->root . '/app/Tabletop/Views/chamber.php'
        );

        self::assertStringContainsString('Invite an Adventurer', $view);
        self::assertStringContainsString('data-gathering-invite-form', $view);
    }

    public function testInvitedPlayerHasTakeSeatExperience(): void
    {
        $view = (string) file_get_contents(
            $this->root . '/app/Tabletop/Views/chamber.php'
        );

        self::assertStringContainsString('Take My Seat', $view);
        self::assertStringContainsString('data-accept-table-invitation', $view);
    }

    public function testClientUsesServerInvitationActions(): void
    {
        $client = (string) file_get_contents(
            $this->root . '/assets/js/tabletop.js'
        );

        self::assertStringContainsString('gmrt_invite_table_player', $client);
        self::assertStringContainsString('gmrt_accept_table_invitation', $client);
    }

    public function testCompanionConnectionIsPresentedAsOptional(): void
    {
        $view = (string) file_get_contents(
            $this->root . '/app/Tabletop/Views/chamber.php'
        );

        self::assertStringContainsString('Great Marketrealm Companion', $view);
        self::assertStringContainsString('Not detected', $view);
    }
}
