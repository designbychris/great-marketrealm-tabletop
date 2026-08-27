<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Presentation;

use PHPUnit\Framework\TestCase;

final class SummonsPresentationTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        $this->root = dirname(__DIR__, 4);
    }

    public function testGatheringUsesRoleSeatTreatment(): void
    {
        $view = (string) file_get_contents($this->root . '/app/Tabletop/Views/chamber.php');
        $css = (string) file_get_contents($this->root . '/assets/css/tabletop.css');

        self::assertStringContainsString('gmrt-party__member--dungeon-master', $css);
        self::assertStringContainsString('gmrt-party__member--player', $css);
    }

    public function testGatheringExplainsEmailSummons(): void
    {
        $view = (string) file_get_contents($this->root . '/app/Tabletop/Views/chamber.php');

        self::assertStringContainsString('The Summons to the Table', $view);
        self::assertStringContainsString('Send Summons', $view);
        self::assertStringContainsString('email a direct Table link', $view);
    }

    public function testIdentityDirectoryOffersAvatarIntegrationFilter(): void
    {
        $source = (string) file_get_contents(
            $this->root . '/app/Tables/Memberships/Repositories/WordPressTableMemberIdentityDirectory.php'
        );

        self::assertStringContainsString('gmrt_table_member_avatar_url', $source);
        self::assertStringContainsString("'email' => \$email", $source);
    }
}
