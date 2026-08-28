<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Presentation;

use PHPUnit\Framework\TestCase;

final class AdventurersSatchelRegressionTest extends TestCase
{
    private function root(string $path): string { return dirname(__DIR__, 4) . '/' . $path; }

    public function testChamberProvidesAccessiblePullOutSatchelForSelectedCharacter(): void
    {
        $view = file_get_contents($this->root('app/Tabletop/Views/chamber.php'));
        self::assertStringContainsString('data-adventurer-satchel', $view);
        self::assertStringContainsString('data-satchel-toggle', $view);
        self::assertStringContainsString('aria-expanded="false"', $view);
        self::assertStringContainsString("\$adventurerPlay['passive_perception']", $view);
    }

    public function testSatchelIsAPlayProjectionNotASecondCharacterStore(): void
    {
        $gateway = file_get_contents($this->root('app/Integration/Companion/WordPressCompanionCharacterGateway.php'));
        $view = file_get_contents($this->root('app/Tabletop/Views/chamber.php'));
        self::assertStringContainsString('gmrc_tabletop_owned_character', $gateway);
        self::assertStringContainsString("\$adventurer['play']", $view);
        self::assertStringNotContainsString('update_post_meta', $view);
    }
}
