<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Architecture;

use PHPUnit\Framework\TestCase;

final class MultilingualCoverageSweepTwoTest extends TestCase
{
    public function test_battlefield_and_keeper_workspace_expose_more_visible_copy_to_gettext(): void
    {
        $root = dirname(__DIR__, 3);
        $view = file_get_contents($root . '/app/Tabletop/Views/chamber.php');

        self::assertIsString($view);
        self::assertStringContainsString("esc_html_e('Begin Battle', 'great-marketrealm-tabletop')", $view);
        self::assertStringContainsString("esc_html_e('End Encounter', 'great-marketrealm-tabletop')", $view);
        self::assertStringContainsString("esc_html_e('Draw Wall', 'great-marketrealm-tabletop')", $view);
        self::assertStringContainsString("esc_html_e(\"The Keeper's Furniture Palette\", 'great-marketrealm-tabletop')", $view);
    }

    public function test_dutch_and_german_catalogues_cover_new_battlefield_strings(): void
    {
        $root = dirname(__DIR__, 3);
        $dutch = file_get_contents($root . '/languages/great-marketrealm-tabletop-nl_NL.po');
        $german = file_get_contents($root . '/languages/great-marketrealm-tabletop-de_DE.po');

        self::assertIsString($dutch);
        self::assertIsString($german);
        self::assertStringContainsString("msgid \"End Encounter\"\nmsgstr \"Ontmoeting beëindigen\"", $dutch);
        self::assertStringContainsString("msgid \"End Encounter\"\nmsgstr \"Begegnung beenden\"", $german);
        self::assertStringContainsString("msgid \"Draw Wall\"\nmsgstr \"Wand zeichnen\"", $german);
    }
}
