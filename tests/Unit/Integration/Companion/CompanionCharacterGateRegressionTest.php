<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Integration\Companion;

use PHPUnit\Framework\TestCase;

final class CompanionCharacterGateRegressionTest extends TestCase
{
    private function root(string $path): string
    {
        return dirname(__DIR__, 4) . '/' . $path;
    }

    public function testCompanionGatewayUsesOwnerScopedBridgeHooks(): void
    {
        $source = file_get_contents($this->root('app/Integration/Companion/WordPressCompanionCharacterGateway.php'));
        self::assertStringContainsString('gmrc_tabletop_owned_characters', $source);
        self::assertStringContainsString('gmrc_tabletop_owned_character', $source);
    }

    public function testCharacterSelectionIsServerValidatedBeforeSeatAssignment(): void
    {
        $source = file_get_contents($this->root('app/Tabletop/Http/CompanionCharacterAjaxController.php'));
        self::assertStringContainsString('characterForUser($userId, $characterId)', $source);
        self::assertStringContainsString('selectCompanionCharacter($tableId, $userId, $characterId)', $source);
    }

    public function testCharacterSelectionPlacesAPlayerControlledCharacterToken(): void
    {
        $source = file_get_contents($this->root('app/Tabletop/Http/CompanionCharacterAjaxController.php'));
        self::assertStringContainsString('TableTokenType::CHARACTER', $source);
        self::assertStringContainsString('$userId,', $source);
        self::assertStringContainsString('$characterId,', $source);
    }

    public function testChamberOffersCharacterPickerAndForgedTokenFace(): void
    {
        $view = file_get_contents($this->root('app/Tabletop/Views/chamber.php'));
        self::assertStringContainsString('data-companion-character-form', $view);
        self::assertStringContainsString('gmrt-token__face', $view);
    }
}
