<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Sessions;

use PHPUnit\Framework\TestCase;

final class DeedsOfSessionRegressionTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        $this->root = dirname(__DIR__, 4);
    }

    public function test_battle_events_carry_immutable_session_identity(): void
    {
        $model = file_get_contents($this->root . '/app/Tabletop/Battle/Models/BattleEvent.php');
        $repository = file_get_contents($this->root . '/app/Tabletop/Battle/Repositories/WordPressBattleEventRepository.php');
        self::assertStringContainsString("'session_id' => \$this->sessionId", $model);
        self::assertStringContainsString('currentForTable', $repository);
        self::assertStringContainsString('forSession', $repository);
    }

    public function test_chamber_chronicle_events_carry_session_identity(): void
    {
        $model = file_get_contents($this->root . '/app/Tabletop/Chronicle/Models/ChamberChronicleEvent.php');
        $repository = file_get_contents($this->root . '/app/Tabletop/Chronicle/Repositories/WordPressChamberChronicleRepository.php');
        self::assertStringContainsString("'session_id' => \$this->sessionId", $model);
        self::assertStringContainsString('currentForTable', $repository);
        self::assertStringContainsString('forSession', $repository);
    }

    public function test_encounters_remember_the_session_that_created_them(): void
    {
        $model = file_get_contents($this->root . '/app/Tabletop/Encounters/Models/Encounter.php');
        $manager = file_get_contents($this->root . '/app/Tabletop/Encounters/Services/EncounterManager.php');
        self::assertStringContainsString("'session_id' => \$this->sessionId", $model);
        self::assertStringContainsString('currentForTable($tableId)?->id()', $manager);
    }

    public function test_live_chronicle_projection_preserves_session_identity(): void
    {
        $battle = file_get_contents($this->root . '/app/Tabletop/Battle/Presentation/BattleLogProjector.php');
        $chamber = file_get_contents($this->root . '/app/Tabletop/Chronicle/Presentation/ChamberChronicleProjector.php');
        self::assertStringContainsString("'session_id' =>", $battle);
        self::assertStringContainsString("'session_id' =>", $chamber);
    }
}
