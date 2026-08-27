<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Http;

use PHPUnit\Framework\TestCase;

final class SummonsArchitectureTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        $this->root = dirname(__DIR__, 4);
    }

    public function testInvitationDeliveryUsesWordPressMailAndAdminSender(): void
    {
        $source = (string) file_get_contents(
            $this->root . '/app/Tables/Memberships/Delivery/WordPressTableInvitationDelivery.php'
        );

        self::assertStringContainsString('wp_mail', $source);
        self::assertStringContainsString("get_option('admin_email'", $source);
        self::assertStringContainsString('From: Great Marketrealm', $source);
    }

    public function testInvitationDeliveryPublishesCompanionFriendlyHook(): void
    {
        $source = (string) file_get_contents(
            $this->root . '/app/Tables/Memberships/Delivery/WordPressTableInvitationDelivery.php'
        );

        self::assertStringContainsString('gmrt_table_invitation_created', $source);
        self::assertStringContainsString('invite_url', $source);
    }

    public function testMailFailureDoesNotUndoCanonicalInvitation(): void
    {
        $source = (string) file_get_contents(
            $this->root . '/app/Tabletop/Http/GatheringAjaxController.php'
        );

        self::assertStringContainsString('invitePlayer', $source);
        self::assertStringContainsString('Their seat is reserved', $source);
        self::assertStringContainsString("'email_sent'", $source);
    }
}
