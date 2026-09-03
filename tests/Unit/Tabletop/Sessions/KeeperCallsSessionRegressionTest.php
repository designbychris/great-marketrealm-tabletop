<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Sessions;

use PHPUnit\Framework\TestCase;

final class KeeperCallsSessionRegressionTest extends TestCase
{
    private function source(string $path): string
    {
        return (string) file_get_contents(dirname(__DIR__, 4) . '/' . $path);
    }

    public function test_session_domain_is_persistent_numbered_and_table_scoped(): void
    {
        $model = $this->source('app/Tabletop/Sessions/Models/TableSession.php');
        $repository = $this->source('app/Tabletop/Sessions/Repositories/WordPressTableSessionRepository.php');
        self::assertStringContainsString('private int $number', $model);
        self::assertStringContainsString("TableSessionStatus::ACTIVE", $model);
        self::assertStringContainsString("private const OPTION = 'gmrt_table_sessions'", $repository);
        self::assertStringContainsString('currentForTable(string $tableId)', $repository);
    }

    public function test_only_the_table_keeper_can_start_and_end_a_session_and_numbers_continue(): void
    {
        $manager = $this->source('app/Tabletop/Sessions/Services/TableSessionManager.php');
        self::assertStringContainsString('dungeonMasterUserId() !== $userId', $manager);
        self::assertStringContainsString("'Session ' . $number", $manager);
        self::assertStringContainsString('$past->number() + 1', $manager);
        self::assertStringContainsString('A Session is already in progress at this Table.', $manager);
        self::assertStringContainsString('There is no active Session to end.', $manager);
    }

    public function test_session_controls_are_nonce_protected_registered_and_keeper_facing(): void
    {
        $controller = $this->source('app/Tabletop/Http/TableSessionAjaxController.php');
        $provider = $this->source('app/Tabletop/TabletopServiceProvider.php');
        $view = $this->source('app/Tabletop/Views/chamber.php');
        self::assertStringContainsString('check_ajax_referer(TabletopAjaxController::NONCE_ACTION', $controller);
        self::assertStringContainsString("'wp_ajax_gmrt_start_table_session'", $provider);
        self::assertStringContainsString("'wp_ajax_gmrt_end_table_session'", $provider);
        self::assertStringContainsString('data-start-table-session', $view);
        self::assertStringContainsString('data-end-table-session', $view);
        self::assertStringContainsString('Between Sessions', $view);
    }

    public function test_active_session_is_projected_into_living_table_and_removed_with_campaign(): void
    {
        $state = $this->source('app/Tabletop/Models/TabletopChamberState.php');
        $ajax = $this->source('app/Tabletop/Http/TabletopAjaxController.php');
        $script = $this->source('assets/js/tabletop.js');
        $remover = $this->source('app/Tabletop/Campaigns/WordPressTabletopRemover.php');
        self::assertStringContainsString('private ?array $session = null', $state);
        self::assertStringContainsString("'session' => \$state->session()", $ajax);
        self::assertStringContainsString('incomingSessionId', $script);
        self::assertStringContainsString("request('gmrt_start_table_session'", $script);
        self::assertStringContainsString("request('gmrt_end_table_session'", $script);
        self::assertStringContainsString("'gmrt_table_sessions'", $remover);
    }
}
