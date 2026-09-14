<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Architecture;

use PHPUnit\Framework\TestCase;

final class SpanishLanguagePackTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        $this->root = dirname(__DIR__, 3);
    }

    public function test_spanish_catalogue_is_bundled_and_compiled(): void
    {
        self::assertFileExists($this->root . '/languages/great-marketrealm-tabletop-es_ES.po');
        self::assertFileExists($this->root . '/languages/great-marketrealm-tabletop-es_ES.mo');
        self::assertGreaterThan(100, filesize($this->root . '/languages/great-marketrealm-tabletop-es_ES.mo'));
    }

    public function test_spanish_catalogue_translates_representative_tabletop_interface_strings(): void
    {
        $po = (string) file_get_contents($this->root . '/languages/great-marketrealm-tabletop-es_ES.po');

        self::assertStringContainsString("msgid \"Exploration Mode\"\nmsgstr \"Modo de exploración\"", $po);
        self::assertStringContainsString("msgid \"The Keeper's Tools\"\nmsgstr \"Las Herramientas del Guardián\"", $po);
        self::assertStringContainsString("msgid \"Start Encounter\"\nmsgstr \"Iniciar encuentro\"", $po);
    }
}
