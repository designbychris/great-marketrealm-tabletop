<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Architecture;

use PHPUnit\Framework\TestCase;

final class InternationalisationFoundationTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        $this->root = dirname(__DIR__, 3);
    }

    public function test_plugin_declares_and_loads_its_language_pack_directory(): void
    {
        $bootstrap = file_get_contents($this->root . '/great-marketrealm-tabletop.php');
        self::assertIsString($bootstrap);
        self::assertStringContainsString('Text Domain: great-marketrealm-tabletop', $bootstrap);
        self::assertStringContainsString('Domain Path: /languages', $bootstrap);
        self::assertStringContainsString("load_plugin_textdomain(\n            'great-marketrealm-tabletop'", $bootstrap);
        self::assertStringContainsString("dirname(plugin_basename(GMRT_FILE)) . '/languages'", $bootstrap);
    }

    public function test_language_pack_contract_is_locale_agnostic_and_separates_content(): void
    {
        self::assertFileExists($this->root . '/languages/README.md');
        self::assertFileExists($this->root . '/docs/Internationalisation.md');

        $guide = file_get_contents($this->root . '/docs/Internationalisation.md');
        self::assertIsString($guide);
        self::assertStringContainsString('Interface translation', $guide);
        self::assertStringContainsString('Canonical content translation', $guide);
        self::assertStringContainsString('Do not add Dutch-specific', $guide);
    }
}
