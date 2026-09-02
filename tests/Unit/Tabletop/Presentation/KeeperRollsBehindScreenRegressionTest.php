<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Presentation;

use PHPUnit\Framework\TestCase;

final class KeeperRollsBehindScreenRegressionTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        $this->root = dirname(__DIR__, 4);
    }

    public function test_keeper_has_a_secret_d20_control_in_the_gathering(): void
    {
        $view = (string) file_get_contents($this->root . '/app/Tabletop/Views/chamber.php');

        self::assertStringContainsString('data-keeper-secret-d20', $view);
        self::assertStringContainsString('data-keeper-secret-d20-result', $view);
        self::assertStringContainsString('Roll a private d20 visible only to the Dungeon Master.', $view);
        self::assertStringContainsString("(\$member['role'] ?? '') === 'dungeon-master'", $view);
    }

    public function test_secret_roll_endpoint_uses_secure_d20_and_requires_active_dm_membership(): void
    {
        $controller = (string) file_get_contents($this->root . '/app/Tabletop/Http/KeeperSecretRollAjaxController.php');
        $provider = (string) file_get_contents($this->root . '/app/Tabletop/TabletopServiceProvider.php');

        self::assertStringContainsString('private D20Roller $roller', $controller);
        self::assertStringContainsString('! $member->isDungeonMaster()', $controller);
        self::assertStringContainsString('TableMemberStatus::ACTIVE', $controller);
        self::assertStringContainsString("'wp_ajax_gmrt_keeper_secret_d20'", $provider);
        self::assertStringContainsString('new SecureD20Roller()', $provider);
    }

    public function test_secret_roll_is_response_only_and_never_a_chronicle_event(): void
    {
        $controller = (string) file_get_contents($this->root . '/app/Tabletop/Http/KeeperSecretRollAjaxController.php');
        $phase = (string) file_get_contents($this->root . '/docs/Roadmap/PHASE-IV.32.3B.md');

        self::assertStringContainsString('intentionally response-only', $controller);
        self::assertStringNotContainsString('TableChronicleRecorder', $controller);
        self::assertStringNotContainsString('BattleEventRepository', $controller);
        self::assertStringContainsString('not persisted to the Chamber Chronicle, Battle Chronicle, Living Table state', $phase);
    }

    public function test_live_gathering_redraw_keeps_the_private_result_only_in_keeper_memory(): void
    {
        $js = (string) file_get_contents($this->root . '/assets/js/tabletop.js');

        self::assertStringContainsString('let keeperSecretD20Result = null;', $js);
        self::assertStringContainsString("request('gmrt_keeper_secret_d20', {})", $js);
        self::assertStringContainsString('populateKeeperSecretRollResult(secretZone);', $js);
        self::assertStringContainsString("root?.dataset.viewerRole !== 'dungeon-master'", $js);
    }

    public function test_secret_roll_has_a_compact_pixel_treatment(): void
    {
        $css = (string) file_get_contents($this->root . '/assets/css/tabletop.css');

        self::assertStringContainsString('IV.32.3B — The Keeper Rolls Behind the Screen', $css);
        self::assertStringContainsString('.gmrt-party__secret-roll {', $css);
        self::assertStringContainsString('.gmrt-party__secret-result:empty {', $css);
    }
}
