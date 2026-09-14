<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Architecture;

use PHPUnit\Framework\TestCase;

final class PortuguesePortugalLanguagePackTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        $this->root = dirname(__DIR__, 3);
    }

    public function test_portuguese_portugal_catalogue_is_bundled_and_compiled(): void
    {
        self::assertFileExists($this->root . '/languages/great-marketrealm-tabletop-pt_PT.po');
        self::assertFileExists($this->root . '/languages/great-marketrealm-tabletop-pt_PT.mo');
        self::assertGreaterThan(100, filesize($this->root . '/languages/great-marketrealm-tabletop-pt_PT.mo'));
    }

    public function test_portuguese_portugal_catalogue_translates_representative_tabletop_interface_strings(): void
    {
        $po = (string) file_get_contents($this->root . '/languages/great-marketrealm-tabletop-pt_PT.po');

        self::assertStringContainsString("msgid \"Exploration Mode\"\nmsgstr \"Modo de Exploração\"", $po);
        self::assertStringContainsString("msgid \"The Keeper's Tools\"\nmsgstr \"As Ferramentas do Guardião\"", $po);
        self::assertStringContainsString("msgid \"Start Encounter\"\nmsgstr \"Iniciar Encontro\"", $po);
        self::assertStringContainsString("msgid \"Furniture duplicated. Pippin is counting again.\"\nmsgstr \"Mobiliário duplicado. Pippin voltou a contar.\"", $po);
        self::assertStringContainsString("msgid \"ACTIVE TURN\"\nmsgstr \"TURNO ATIVO\"", $po);
        self::assertStringContainsString("msgid \"NO TARGET SELECTED\"\nmsgstr \"NENHUM ALVO SELECIONADO\"", $po);
    }
}
