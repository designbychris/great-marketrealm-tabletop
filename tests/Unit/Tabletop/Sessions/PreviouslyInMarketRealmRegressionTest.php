<?php

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Sessions;

use PHPUnit\Framework\TestCase;

final class PreviouslyInMarketRealmRegressionTest extends TestCase
{
    private function source(string $path): string { return (string) file_get_contents(dirname(__DIR__, 4) . '/' . $path); }

    public function test_completed_session_builds_recap_from_session_scoped_evidence(): void
    {
        $source = $this->source('app/Tabletop/Sessions/Services/SessionRecapBuilder.php');
        self::assertStringContainsString('forSession($session->tableId(), $session->id())', $source);
        self::assertStringContainsString('array_slice($facts, 0, 12)', $source);
    }

    public function test_ending_session_persists_keeper_draft(): void
    {
        $source = $this->source('app/Tabletop/Sessions/Services/TableSessionManager.php');
        self::assertStringContainsString('$this->recapBuilder->build($session)', $source);
        self::assertStringContainsString('$this->recaps->save', $source);
    }

    public function test_chamber_presents_the_marketrealm_recap(): void
    {
        $source = $this->source('app/Tabletop/Views/chamber.php');
        self::assertStringContainsString('Previously, in the MarketRealm…', $source);
        self::assertStringContainsString("Pippin's field notes · Keeper draft", $source);
    }

    public function test_viewer_status_anchors_the_command_header_after_session_controls(): void
    {
        $source = $this->source('app/Tabletop/Views/chamber.php');
        $viewer = strpos($source, 'class="gmrt-chamber__viewer" aria-label="Table role and status"');
        $session = strpos($source, 'class="gmrt-table-session" data-table-session');

        self::assertNotFalse($viewer);
        self::assertNotFalse($session);
        self::assertLessThan($viewer, $session);
    }

    public function test_recap_reuses_the_canonical_pixel_pippin_asset(): void
    {
        $source = $this->source('app/Tabletop/Views/chamber.php');
        self::assertStringContainsString("assets/images/pippin-peppercorn-pixel.png", $source);
        self::assertStringContainsString('gmrt-session-recap__pippin', $source);
        self::assertStringContainsString('gmrt-session-recap__bubble', $source);
    }

    public function test_recap_has_an_accessible_show_hide_control(): void
    {
        $source = $this->source('app/Tabletop/Views/chamber.php');
        self::assertStringContainsString('data-session-recap-toggle', $source);
        self::assertStringContainsString('aria-expanded="true"', $source);
        self::assertStringContainsString('aria-controls="gmrt-session-recap-content"', $source);
        self::assertStringContainsString('data-session-recap-content', $source);
    }

    public function test_recap_collapse_preference_is_remembered_per_session(): void
    {
        $source = $this->source('assets/js/tabletop.js');
        self::assertStringContainsString('sessionRecapStorageKey', $source);
        self::assertStringContainsString('dataset.sessionRecapId', $source);
        self::assertStringContainsString('window.localStorage.setItem', $source);
        self::assertStringContainsString('restoreSessionRecapPreference()', $source);
    }


    public function test_choose_battlemap_lives_inside_dungeon_master_controls(): void
    {
        $source = $this->source('app/Tabletop/Views/chamber.php');
        $controls = strpos($source, 'class="gmrt-keeper-controls__body"');
        $battlemap = strpos($source, 'data-choose-battlemap');
        $boardHeading = strpos($source, 'class="gmrt-board__heading"');

        self::assertNotFalse($controls);
        self::assertNotFalse($battlemap);
        self::assertNotFalse($boardHeading);
        self::assertGreaterThan($controls, $battlemap);
    }

    public function test_recap_is_not_companion_published_automatically(): void
    {
        $source = $this->source('app/Tabletop/Sessions/Services/SessionRecapBuilder.php');
        self::assertStringNotContainsString('apply_filters', $source);
        self::assertStringNotContainsString('CompanionCampaignBridge', $source);
    }
}
