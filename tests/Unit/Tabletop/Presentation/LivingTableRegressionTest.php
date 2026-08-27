<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Presentation;

use PHPUnit\Framework\TestCase;

final class LivingTableRegressionTest extends TestCase
{
    private string $view;
    private string $script;
    private string $css;

    protected function setUp(): void
    {
        $root = dirname(__DIR__, 4);
        $this->view = (string) file_get_contents($root . '/app/Tabletop/Views/chamber.php');
        $this->script = (string) file_get_contents($root . '/assets/js/tabletop.js');
        $this->css = (string) file_get_contents($root . '/assets/css/tabletop.css');
    }

    public function testRoundHasLiveUpdateHook(): void
    {
        self::assertStringContainsString('data-live-round', $this->view);
    }

    public function testCurrentCombatantHasLiveUpdateHook(): void
    {
        self::assertStringContainsString('data-live-current-combatant', $this->view);
    }

    public function testRemoteRevisionPatchesRoundWithoutReload(): void
    {
        self::assertStringContainsString("liveRound.textContent = 'Round ' + String(incomingEncounter.round || 0)", $this->script);
        self::assertStringContainsString('encounterRevisionChanged && currentEncounter && incomingEncounter', $this->script);
    }

    public function testRemoteRevisionPatchesCurrentCombatant(): void
    {
        self::assertStringContainsString("activeToken.label || 'Unknown combatant'", $this->script);
    }

    public function testRemoteRevisionMovesActiveTurnMarker(): void
    {
        self::assertStringContainsString("'is-active-turn'", $this->script);
        self::assertStringContainsString('.gmrt-token.is-active-turn', $this->css);
    }

    public function testEncounterLifecycleChangeUsesLiveChamberTransition(): void
    {
        self::assertStringContainsString('if (encounterLifecycleChanged)', $this->script);
        self::assertStringContainsString('await replaceChamber(', $this->script);
        self::assertStringNotContainsString("say('The Table has changed its battle state. Reopening the chamber…')", $this->script);
    }

    public function testRemoteTurnChangeHasAccessibleStatusAnnouncement(): void
    {
        self::assertStringContainsString("say('The Table stirred — the turn has changed.')", $this->script);
    }
}
