<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Chronicle;

use PHPUnit\Framework\TestCase;

final class ChroniclesOfTheTableRegressionTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        $this->root = dirname(__DIR__, 4);
    }

    public function testAllSatchelEndpointsUseOneChronicleRecorder(): void
    {
        foreach (['QuickHandsAjaxController.php', 'WeaponHandsAjaxController.php', 'SpellPouchAjaxController.php'] as $file) {
            $source = (string) file_get_contents($this->root . '/app/Tabletop/Http/' . $file);
            self::assertStringContainsString('TableChronicleRecorder', $source);
            self::assertStringContainsString('recordSatchelRoll(', $source);
        }

        $provider = (string) file_get_contents($this->root . '/app/Tabletop/TabletopServiceProvider.php');
        self::assertSame(1, substr_count($provider, '$chronicle = new TableChronicleRecorder('));
    }

    public function testRecorderRoutesEncounterAndPeaceRollsToDifferentChronicles(): void
    {
        $source = (string) file_get_contents($this->root . '/app/Tabletop/Chronicle/Services/TableChronicleRecorder.php');
        self::assertStringContainsString('currentForScene(', $source);
        self::assertStringContainsString("'satchel-roll'", $source);
        self::assertStringContainsString('$this->battleEvents->append(', $source);
        self::assertStringContainsString('$this->chamberEvents->append(', $source);
        self::assertStringContainsString('new ChamberChronicleEvent(', $source);
    }

    public function testChamberChronicleIsPersistentAndPartOfLivingState(): void
    {
        $repository = (string) file_get_contents($this->root . '/app/Tabletop/Chronicle/Repositories/WordPressChamberChronicleRepository.php');
        $state = (string) file_get_contents($this->root . '/app/Tabletop/Models/TabletopChamberState.php');
        $ajax = (string) file_get_contents($this->root . '/app/Tabletop/Http/TabletopAjaxController.php');
        $javascript = (string) file_get_contents($this->root . '/assets/js/tabletop.js');

        self::assertStringContainsString("gmrt_chamber_chronicle", $repository);
        self::assertStringContainsString('update_option(', $repository);
        self::assertStringContainsString("'chamber_log' => \$this->chamberLog", $state);
        self::assertStringContainsString("'chamber_log' => \$state->chamberLog()", $ajax);
        self::assertStringContainsString('renderChronicle(state.encounter ? state.battle_log : state.chamber_log', $javascript);
        self::assertSame(1, substr_count($javascript, 'window.setInterval(refresh, 5000)'));
    }

    public function testChroniclePresentationSwitchesBetweenBattleAndChamber(): void
    {
        $view = (string) file_get_contents($this->root . '/app/Tabletop/Views/chamber.php');
        $projector = (string) file_get_contents($this->root . '/app/Tabletop/Battle/Presentation/BattleLogProjector.php');

        self::assertStringContainsString('Battle Chronicle', $view);
        self::assertStringContainsString('Chamber Chronicle', $view);
        self::assertStringContainsString('Tales from the Chamber', $view);
        self::assertStringContainsString('data-table-chronicle', $view);
        self::assertStringContainsString("\$type === 'satchel-roll'", $projector);
        self::assertStringContainsString("\$payload['summary']", $projector);
    }
}
