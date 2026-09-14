<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Architecture;

use PHPUnit\Framework\TestCase;

final class PortugueseBrazilLanguagePackTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        $this->root = dirname(__DIR__, 3);
    }

    public function test_portuguese_brazil_catalogue_is_bundled_and_compiled(): void
    {
        self::assertFileExists($this->root . '/languages/great-marketrealm-tabletop-pt_BR.po');
        self::assertFileExists($this->root . '/languages/great-marketrealm-tabletop-pt_BR.mo');
        self::assertGreaterThan(100, filesize($this->root . '/languages/great-marketrealm-tabletop-pt_BR.mo'));
    }

    public function test_portuguese_brazil_catalogue_uses_brazilian_interface_vocabulary(): void
    {
        $po = (string) file_get_contents($this->root . '/languages/great-marketrealm-tabletop-pt_BR.po');

        self::assertStringContainsString("msgid \"Password\"\nmsgstr \"Senha\"", $po);
        self::assertStringContainsString("msgid \"Username or email\"\nmsgstr \"Nome de usuário ou email\"", $po);
        self::assertStringContainsString("msgid \"Calibrate Grid\"\nmsgstr \"Calibrar Grade\"", $po);
        self::assertStringContainsString("msgid \"Saved Atlas map\"\nmsgstr \"Mapa salvo do Atlas\"", $po);
        self::assertStringContainsString("msgid \"Save\"\nmsgstr \"Salvar\"", $po);
    }
}
