<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Chronicle;

use PHPUnit\Framework\TestCase;

final class PippinComparesNotesRegressionTest extends TestCase
{
    private function root(string $path): string
    {
        return dirname(__DIR__, 4) . '/' . ltrim($path, '/');
    }

    public function test_progress_projector_compares_prepared_and_actual_records(): void
    {
        $source = (string) file_get_contents($this->root('app/Tabletop/Chronicle/Presentation/AdventureProgressProjector.php'));

        self::assertStringContainsString("'prepared' => \$prepared", $source);
        self::assertStringContainsString("'actual' => \$actual", $source);
        self::assertStringContainsString("'fact_counts' => \$factCounts", $source);
    }

    public function test_projector_understands_story_beat_statuses(): void
    {
        $source = (string) file_get_contents($this->root('app/Tabletop/Chronicle/Presentation/AdventureProgressProjector.php'));

        self::assertStringContainsString("['pending' => 0, 'active' => 0, 'resolved' => 0]", $source);
        self::assertStringContainsString("\$beat['status'] ?? 'pending'", $source);
    }

    public function test_projector_only_uses_structured_adventure_facts(): void
    {
        $source = (string) file_get_contents($this->root('app/Tabletop/Chronicle/Presentation/AdventureProgressProjector.php'));

        self::assertStringContainsString("!== 'adventure'", $source);
        self::assertStringContainsString("\$record['payload']['adventure']", $source);
    }

    public function test_progress_is_built_from_the_current_session_only(): void
    {
        $source = (string) file_get_contents($this->root('app/Tabletop/Services/TabletopChamber.php'));

        self::assertStringContainsString('$this->chamberEvents->forSession($tableId, $currentSession->id())', $source);
    }

    public function test_progress_projection_is_keeper_only(): void
    {
        $source = (string) file_get_contents($this->root('app/Tabletop/Services/TabletopChamber.php'));

        self::assertStringContainsString("'adventure_progress' => \$viewer->isDungeonMaster() ? \$adventureProgress : []", $source);
    }

    public function test_raw_adventure_chronicle_entries_are_removed_from_player_chamber_log(): void
    {
        $source = (string) file_get_contents($this->root('app/Tabletop/Services/TabletopChamber.php'));

        self::assertStringContainsString("(\$entry['kind'] ?? '') !== 'adventure'", $source);
    }

    public function test_keeper_view_has_plan_and_reality_columns(): void
    {
        $view = (string) file_get_contents($this->root('app/Tabletop/Views/chamber.php'));

        self::assertStringContainsString('data-adventure-progress', $view);
        self::assertStringContainsString('Prepared Route', $view);
        self::assertStringContainsString('What Actually Happened', $view);
    }

    public function test_keeper_view_surfaces_adventure_fact_totals(): void
    {
        $view = (string) file_get_contents($this->root('app/Tabletop/Views/chamber.php'));

        self::assertStringContainsString('secrets revealed', $view);
        self::assertStringContainsString('traps triggered', $view);
        self::assertStringContainsString('treasures claimed', $view);
        self::assertStringContainsString('beats resolved', $view);
    }

    public function test_factory_supplies_the_progress_projector(): void
    {
        $factory = (string) file_get_contents($this->root('app/Tabletop/Services/TabletopChamberFactory.php'));

        self::assertStringContainsString('AdventureProgressProjector', $factory);
        self::assertStringContainsString('new AdventureProgressProjector()', $factory);
    }

    public function test_progress_panel_is_responsive(): void
    {
        $css = (string) file_get_contents($this->root('assets/css/tabletop.css'));

        self::assertStringContainsString('.gmrt-adventure-progress__columns', $css);
        self::assertStringContainsString('@media (max-width: 760px)', $css);
    }
}
