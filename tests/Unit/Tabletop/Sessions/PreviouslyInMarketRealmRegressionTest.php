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

    public function test_recap_is_not_companion_published_automatically(): void
    {
        $source = $this->source('app/Tabletop/Sessions/Services/SessionRecapBuilder.php');
        self::assertStringNotContainsString('apply_filters', $source);
        self::assertStringNotContainsString('CompanionCampaignBridge', $source);
    }
}
