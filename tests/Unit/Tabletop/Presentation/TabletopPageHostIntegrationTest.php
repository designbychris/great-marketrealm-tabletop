<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Presentation;

use PHPUnit\Framework\TestCase;

final class TabletopPageHostIntegrationTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        $this->root = dirname(__DIR__, 4);
    }

    public function testProviderRegistersCompanionStyleShortcode(): void
    {
        $source = (string) file_get_contents(
            $this->root
                . '/app/Tabletop/TabletopServiceProvider.php'
        );

        self::assertStringContainsString(
            'add_shortcode(',
            $source
        );
        self::assertStringContainsString(
            'TabletopShortcode::TAG',
            $source
        );
        self::assertStringNotContainsString(
            "'template_redirect'",
            $source
        );
        self::assertStringNotContainsString(
            "'query_vars'",
            $source
        );
    }

    public function testCanonicalShortcodeNameIsStable(): void
    {
        $source = (string) file_get_contents(
            $this->root
                . '/app/Tabletop/Presentation/TabletopShortcode.php'
        );

        self::assertStringContainsString(
            "public const TAG = 'great_marketrealm_tabletop'",
            $source
        );
    }

    public function testShortcodeSupportsAttributeOrQueryTableSelection(): void
    {
        $source = (string) file_get_contents(
            $this->root
                . '/app/Tabletop/Presentation/TabletopShortcode.php'
        );

        self::assertStringContainsString(
            "shortcode_atts(",
            $source
        );
        self::assertStringContainsString(
            "'table' => ''",
            $source
        );
        self::assertStringContainsString(
            "\$_GET['table']",
            $source
        );
    }

    public function testShortcodeOwnsTabletopAssetsNotThemeShell(): void
    {
        $source = (string) file_get_contents(
            $this->root
                . '/app/Tabletop/Presentation/TabletopShortcode.php'
        );
        $view = (string) file_get_contents(
            $this->root
                . '/app/Tabletop/Views/chamber.php'
        );

        self::assertStringContainsString(
            'wp_enqueue_style(',
            $source
        );
        self::assertStringContainsString(
            'wp_enqueue_script(',
            $source
        );
        self::assertStringNotContainsString(
            'get_header();',
            $view
        );
        self::assertStringNotContainsString(
            'get_footer();',
            $view
        );
    }

    public function testProviderRegistersBattleDeedEndpoint(): void
    {
        $source = (string) file_get_contents(
            $this->root
                . '/app/Tabletop/TabletopServiceProvider.php'
        );

        self::assertStringContainsString(
            "'wp_ajax_gmrt_perform_battle_deed'",
            $source
        );
        self::assertStringNotContainsString(
            "'wp_ajax_nopriv_gmrt_perform_battle_deed'",
            $source
        );
    }

}
