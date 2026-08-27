<?php
declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class CartographySecurityRegressionTest extends TestCase
{
    public function testCartographyRequiresDungeonMasterRole(): void
    {
        $source = (string) file_get_contents(
            dirname(__DIR__, 4)
            . '/app/Tabletop/Cartography/Services/CartographersTable.php'
        );

        self::assertStringContainsString(
            'TableMemberRole::DUNGEON_MASTER',
            $source
        );
        self::assertStringContainsString(
            'TableMemberStatus::ACTIVE',
            $source
        );
    }

    public function testBattlemapAjaxUsesTabletopNonce(): void
    {
        $source = (string) file_get_contents(
            dirname(__DIR__, 4)
            . '/app/Tabletop/Http/CartographyAjaxController.php'
        );

        self::assertStringContainsString(
            'check_ajax_referer(',
            $source
        );
        self::assertStringContainsString(
            'TabletopAjaxController::NONCE_ACTION',
            $source
        );
    }

    public function testBattlemapInspectorAcceptsImagesOnly(): void
    {
        $source = (string) file_get_contents(
            dirname(__DIR__, 4)
            . '/app/Tabletop/Cartography/Services/BattlemapInspector.php'
        );

        self::assertStringContainsString(
            'wp_attachment_is_image($attachmentId)',
            $source
        );
        self::assertStringContainsString(
            'wp_get_attachment_metadata($attachmentId)',
            $source
        );
    }
}
