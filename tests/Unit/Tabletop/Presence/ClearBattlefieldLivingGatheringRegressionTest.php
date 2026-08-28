<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Presence;

use PHPUnit\Framework\TestCase;

final class ClearBattlefieldLivingGatheringRegressionTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        $this->root = dirname(__DIR__, 4);
    }

    public function testTokenRemovalIsAuthenticatedServerAuthoritativeAndOwnershipChecked(): void
    {
        $provider = (string) file_get_contents($this->root . '/app/Tabletop/TabletopServiceProvider.php');
        $service = (string) file_get_contents($this->root . '/app/Tabletop/Tokens/Services/TableTokenRemoval.php');
        $client = (string) file_get_contents($this->root . '/assets/js/tabletop.js');

        self::assertStringContainsString('wp_ajax_gmrt_remove_chamber_token', $provider);
        self::assertStringNotContainsString('wp_ajax_nopriv_gmrt_remove_chamber_token', $provider);
        self::assertStringContainsString('isDungeonMaster()', $service);
        self::assertStringContainsString('TableTokenType::CHARACTER', $service);
        self::assertStringContainsString('controllerUserId() === $userId', $service);
        self::assertStringContainsString('End the current Encounter before removing', $service);
        self::assertStringContainsString("request('gmrt_remove_chamber_token'", $client);
    }

    public function testGatheringIsPatchedFromExistingLivingTableHeartbeat(): void
    {
        $view = (string) file_get_contents($this->root . '/app/Tabletop/Views/chamber.php');
        $client = (string) file_get_contents($this->root . '/assets/js/tabletop.js');
        $state = (string) file_get_contents($this->root . '/app/Tabletop/Http/TabletopAjaxController.php');

        self::assertStringContainsString('data-live-gathering-list', $view);
        self::assertStringContainsString('renderGathering(state.members)', $client);
        self::assertStringContainsString('setInterval(refresh, 5000)', $client);
        self::assertStringContainsString("'members' => \$state->members()", $state);
        self::assertStringNotContainsString('setInterval(refreshGathering', $client);
    }

    public function testUnselectedCompanionCharacterGetsObviousAdventurerCallout(): void
    {
        $view = (string) file_get_contents($this->root . '/app/Tabletop/Views/chamber.php');

        self::assertStringContainsString('Choose Your Adventurer', $view);
        self::assertStringContainsString('gmrt-character-gate__callout', $view);
        self::assertStringContainsString('$selectedCharacter === null', $view);
    }
}
