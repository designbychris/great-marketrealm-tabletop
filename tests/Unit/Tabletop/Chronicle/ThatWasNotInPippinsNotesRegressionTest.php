<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Chronicle;

use PHPUnit\Framework\TestCase;

final class ThatWasNotInPippinsNotesRegressionTest extends TestCase
{
    private function root(string $path): string
    {
        return dirname(__DIR__, 4) . '/' . ltrim($path, '/');
    }

    public function test_adventure_facts_use_the_existing_chamber_chronicle(): void
    {
        $source = (string) file_get_contents($this->root('app/Tabletop/Chronicle/Services/AdventureEventRecorder.php'));

        self::assertStringContainsString('ChamberChronicleRepository', $source);
        self::assertStringContainsString('new ChamberChronicleEvent(', $source);
        self::assertStringContainsString("'adventure'", $source);
        self::assertStringContainsString("['adventure' => \$payload]", $source);
    }

    public function test_adventure_recorder_does_not_create_a_parallel_repository(): void
    {
        self::assertFileDoesNotExist($this->root('app/Tabletop/Chronicle/Contracts/AdventureEventRepository.php'));
        self::assertFileDoesNotExist($this->root('app/Tabletop/Chronicle/Repositories/WordPressAdventureEventRepository.php'));
    }

    public function test_secret_revelation_becomes_a_structured_adventure_fact(): void
    {
        $source = (string) file_get_contents($this->root('app/Tabletop/Http/DungeonForgeAjaxController.php'));

        self::assertStringContainsString("'secret-revealed'", $source);
        self::assertStringContainsString("'secret_id' => \$secretId", $source);
    }

    public function test_manual_trap_trigger_becomes_a_structured_adventure_fact(): void
    {
        $source = (string) file_get_contents($this->root('app/Tabletop/Http/DungeonForgeAjaxController.php'));

        self::assertStringContainsString("'trap-triggered'", $source);
        self::assertStringContainsString("'source' => 'keeper'", $source);
    }

    public function test_movement_trap_trigger_uses_the_same_adventure_boundary(): void
    {
        $source = (string) file_get_contents($this->root('app/Tabletop/Cartography/Services/ForgeTrapTrigger.php'));

        self::assertStringContainsString('AdventureEventRecorder', $source);
        self::assertStringContainsString("'trap-triggered'", $source);
        self::assertStringContainsString("'source' => 'movement'", $source);
        self::assertStringContainsString("'token_id' => \$token->id()", $source);
    }

    public function test_looted_treasure_becomes_a_structured_adventure_fact(): void
    {
        $source = (string) file_get_contents($this->root('app/Tabletop/Http/DungeonForgeAjaxController.php'));

        self::assertStringContainsString("'treasure-looted'", $source);
        self::assertStringContainsString("'treasure_id' => \$treasureId", $source);
    }

    public function test_resolved_story_beat_becomes_a_structured_adventure_fact(): void
    {
        $source = (string) file_get_contents($this->root('app/Tabletop/Http/DungeonForgeAjaxController.php'));

        self::assertStringContainsString("'story-beat-resolved'", $source);
        self::assertStringContainsString("'beat_index' => \$beatIndex", $source);
        self::assertStringContainsString("'stage' => \$stage", $source);
    }

    public function test_forge_controller_receives_the_shared_adventure_recorder(): void
    {
        $provider = (string) file_get_contents($this->root('app/Tabletop/TabletopServiceProvider.php'));

        self::assertStringContainsString('new AdventureEventRecorder(', $provider);
        self::assertStringContainsString('new WordPressChamberChronicleRepository()', $provider);
        self::assertStringContainsString('new SystemTableClock()', $provider);
    }

    public function test_movement_factory_receives_the_shared_adventure_recorder(): void
    {
        $factory = (string) file_get_contents($this->root('app/Tabletop/Movement/Services/TabletopMovementFactory.php'));

        self::assertStringContainsString('new AdventureEventRecorder(', $factory);
        self::assertStringContainsString('new ForgeTrapTrigger(', $factory);
    }

    public function test_session_recap_already_reads_chamber_chronicle_facts(): void
    {
        $source = (string) file_get_contents($this->root('app/Tabletop/Sessions/Services/SessionRecapBuilder.php'));

        self::assertStringContainsString('$this->chamber->forSession(', $source);
        self::assertStringContainsString("\$record['summary']", $source);
    }
}
