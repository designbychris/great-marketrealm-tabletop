<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Satchel;

use PHPUnit\Framework\TestCase;

final class SatchelLeavesWithCharacterRegressionTest extends TestCase
{
    private function root(string $path): string { return dirname(__DIR__, 4) . '/' . $path; }

    public function test_selected_character_requires_the_viewers_character_token_to_be_present(): void
    {
        $chamber = file_get_contents($this->root('app/Tabletop/Services/TabletopChamber.php'));
        self::assertStringContainsString('$viewerCharacterPresent = false', $chamber);
        self::assertStringContainsString('&& $viewerCharacterPresent', $chamber);
        self::assertStringContainsString('$tokenModel->controllerUserId() === $viewerUserId', $chamber);
    }

    public function test_live_refresh_removes_an_orphaned_satchel(): void
    {
        $js = file_get_contents($this->root('assets/js/tabletop.js'));
        self::assertStringContainsString("state.integrations?.companion?.selected_character", $js);
        self::assertStringContainsString("document.querySelector('[data-adventurer-satchel]')", $js);
        self::assertStringContainsString('satchel.remove()', $js);
    }
}
