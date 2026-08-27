<?php
declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class CartographyPresentationRegressionTest extends TestCase
{
    public function testDungeonMasterHasBattlemapChooser(): void
    {
        $source = (string) file_get_contents(
            dirname(__DIR__, 4)
            . '/app/Tabletop/Views/chamber.php'
        );

        self::assertStringContainsString(
            'data-choose-battlemap',
            $source
        );
        self::assertStringContainsString(
            '$state->isDungeonMaster()',
            $source
        );
    }

    public function testShortcodeLoadsWordPressMediaLibrary(): void
    {
        $source = (string) file_get_contents(
            dirname(__DIR__, 4)
            . '/app/Tabletop/Presentation/TabletopShortcode.php'
        );

        self::assertStringContainsString(
            'wp_enqueue_media();',
            $source
        );
    }

    public function testClientUsesImageOnlyMediaPicker(): void
    {
        $source = (string) file_get_contents(
            dirname(__DIR__, 4)
            . '/assets/js/tabletop.js'
        );

        self::assertStringContainsString(
            "title: 'Choose a Battlemap'",
            $source
        );
        self::assertStringContainsString(
            "type: 'image'",
            $source
        );
        self::assertStringContainsString(
            "'gmrt_replace_battlemap'",
            $source
        );
    }

    public function testMapCanRefreshWithoutMovingTokens(): void
    {
        $source = (string) file_get_contents(
            dirname(__DIR__, 4)
            . '/assets/js/tabletop.js'
        );

        self::assertStringContainsString(
            "map.src = String(battlemap.url)",
            $source
        );
        self::assertStringNotContainsString(
            "gmrt_move_token', {\n                            attachment_id",
            $source
        );
    }
}
