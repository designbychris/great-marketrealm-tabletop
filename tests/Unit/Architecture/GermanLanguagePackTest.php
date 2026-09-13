<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Architecture;

use PHPUnit\Framework\TestCase;

final class GermanLanguagePackTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        $this->root = dirname(__DIR__, 3);
    }

    public function test_german_po_and_mo_catalogues_are_shipped(): void
    {
        self::assertFileExists($this->root . '/languages/great-marketrealm-tabletop-de_DE.po');
        self::assertFileExists($this->root . '/languages/great-marketrealm-tabletop-de_DE.mo');
        self::assertGreaterThan(100, filesize($this->root . '/languages/great-marketrealm-tabletop-de_DE.mo'));
    }

    public function test_german_catalogue_translates_representative_keeper_interface_strings(): void
    {
        $po = file_get_contents($this->root . '/languages/great-marketrealm-tabletop-de_DE.po');
        self::assertIsString($po);
        self::assertStringContainsString("msgid \"Session\"\nmsgstr \"Sitzung\"", $po);
        self::assertStringContainsString("msgid \"Tools\"\nmsgstr \"Werkzeuge\"", $po);
        self::assertStringContainsString("msgid \"Bestiary\"\nmsgstr \"Bestiarium\"", $po);
        self::assertStringContainsString("msgid \"End Session\"\nmsgstr \"Sitzung beenden\"", $po);
    }
}
