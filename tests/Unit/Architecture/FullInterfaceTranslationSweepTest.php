<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Architecture;

use PHPUnit\Framework\TestCase;

final class FullInterfaceTranslationSweepTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        $this->root = dirname(__DIR__, 3);
    }

    public function test_primary_battlefield_and_gathering_copy_is_translatable(): void
    {
        $view = file_get_contents($this->root . '/app/Tabletop/Views/chamber.php');
        self::assertIsString($view);
        self::assertStringContainsString("esc_html_e('Exploration Mode', 'great-marketrealm-tabletop')", $view);
        self::assertStringContainsString("esc_html_e('Start Encounter', 'great-marketrealm-tabletop')", $view);
        self::assertStringContainsString("esc_html_e('Adventurers at the Table', 'great-marketrealm-tabletop')", $view);
        self::assertStringContainsString("esc_html_e('Your Fellowship Ribbon', 'great-marketrealm-tabletop')", $view);
    }

    public function test_atlas_bestiary_and_scene_tools_continue_the_translation_sweep(): void
    {
        $view = file_get_contents($this->root . '/app/Tabletop/Views/chamber.php');
        self::assertIsString($view);
        self::assertStringContainsString("esc_html_e('Generate Scene', 'great-marketrealm-tabletop')", $view);
        self::assertStringContainsString("esc_html_e('Search the shelves', 'great-marketrealm-tabletop')", $view);
        self::assertStringContainsString("esc_html_e('Snap to Grid', 'great-marketrealm-tabletop')", $view);
    }

    public function test_dutch_catalogue_contains_new_battlefield_strings(): void
    {
        $po = file_get_contents($this->root . '/languages/great-marketrealm-tabletop-nl_NL.po');
        self::assertIsString($po);
        self::assertStringContainsString('msgid "Adventurers at the Table"', $po);
        self::assertStringContainsString('msgstr "Avonturiers aan de Tafel"', $po);
    }
}
