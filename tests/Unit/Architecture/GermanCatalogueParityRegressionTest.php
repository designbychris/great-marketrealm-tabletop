<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Architecture;

use PHPUnit\Framework\TestCase;

final class GermanCatalogueParityRegressionTest extends TestCase
{
    public function test_german_catalogue_covers_the_second_sweep_interface_titles(): void
    {
        $root = dirname(__DIR__, 3);
        $po = (string) file_get_contents($root . '/languages/great-marketrealm-tabletop-de_DE.po');

        self::assertStringContainsString("msgid \"Behind the Curtain\"\nmsgstr \"Hinter dem Vorhang\"", $po);
        self::assertStringContainsString("msgid \"Adventurers at the Table\"\nmsgstr \"Abenteurer am Tisch\"", $po);
        self::assertStringContainsString("msgid \"Forge New Scene\"\nmsgstr \"Neue Szene schmieden\"", $po);
    }
}
