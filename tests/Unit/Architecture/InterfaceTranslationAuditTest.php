<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Architecture;

use PHPUnit\Framework\TestCase;

final class InterfaceTranslationAuditTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        $this->root = dirname(__DIR__, 3);
    }

    public function test_browser_copy_has_a_php_translation_catalogue(): void
    {
        $catalogue = file_get_contents($this->root . '/app/Tabletop/Presentation/TabletopI18n.php');
        self::assertIsString($catalogue);
        self::assertStringContainsString("__('Show Recap', 'great-marketrealm-tabletop')", $catalogue);
        self::assertStringContainsString("__('The Session could not be ended.', 'great-marketrealm-tabletop')", $catalogue);
        self::assertStringContainsString("__('Pippin could not rearrange that furnishing.', 'great-marketrealm-tabletop')", $catalogue);
    }

    public function test_both_tabletop_entry_points_supply_the_translation_catalogue(): void
    {
        foreach ([
            '/app/Tabletop/Presentation/TabletopShortcode.php',
            '/app/Tabletop/Http/TabletopController.php',
        ] as $relative) {
            $source = file_get_contents($this->root . $relative);
            self::assertIsString($source);
            self::assertStringContainsString("'strings' => TabletopI18n::strings()", $source);
        }
    }

    public function test_keeper_workspace_markup_uses_the_tabletop_text_domain(): void
    {
        $view = file_get_contents($this->root . '/app/Tabletop/Views/chamber.php');
        self::assertIsString($view);
        self::assertStringContainsString("esc_html_e('The Session Desk', 'great-marketrealm-tabletop')", $view);
        self::assertStringContainsString("esc_html_e('Session', 'great-marketrealm-tabletop')", $view);
        self::assertStringContainsString("esc_html_e('Tools', 'great-marketrealm-tabletop')", $view);
        self::assertStringContainsString("esc_html_e('Atlas', 'great-marketrealm-tabletop')", $view);
        self::assertStringContainsString("esc_html_e('Bestiary', 'great-marketrealm-tabletop')", $view);
    }
}
