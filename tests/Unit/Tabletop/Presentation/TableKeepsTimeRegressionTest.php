<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Presentation;

use PHPUnit\Framework\TestCase;

final class TableKeepsTimeRegressionTest extends TestCase
{
    private string $view;
    private string $script;
    private string $controller;
    private string $state;

    protected function setUp(): void
    {
        $root = dirname(__DIR__, 4);
        $this->view = (string) file_get_contents($root . '/app/Tabletop/Views/chamber.php');
        $this->script = (string) file_get_contents($root . '/assets/js/tabletop.js');
        $this->controller = (string) file_get_contents($root . '/app/Tabletop/Http/TabletopAjaxController.php');
        $this->state = (string) file_get_contents($root . '/app/Tabletop/Models/TabletopChamberState.php');
    }

    public function testChamberCarriesServerSharedStateRevision(): void
    {
        self::assertStringContainsString('data-sync-revision', $this->view);
        self::assertStringContainsString('$state?->syncRevision()', $this->view);
    }

    public function testStateHeartbeatReturnsSharedRevision(): void
    {
        self::assertStringContainsString("'sync_revision' => \$state->syncRevision()", $this->controller);
    }

    public function testSharedRevisionCoversEncounterAndExplorationState(): void
    {
        self::assertStringContainsString("'encounter' => \$this->encounter", $this->state);
        self::assertStringContainsString("'fog' => \$this->fog", $this->state);
        self::assertStringContainsString("'vision_layer' => \$this->visionLayer", $this->state);
    }

    public function testHeartbeatDetectsEncounterRevisionChanges(): void
    {
        self::assertStringContainsString('currentEncounterRevision !== incomingEncounterRevision', $this->script);
        self::assertStringContainsString('currentEncounterId !== incomingEncounterId', $this->script);
    }

    public function testRemoteEncounterChangeUpdatesLivingTableInPlace(): void
    {
        self::assertStringContainsString("liveRound.textContent = 'Round ' + String(incomingEncounter.round || 0);", $this->script);
        self::assertStringContainsString('currentEncounter.dataset.encounterRevision = incomingEncounterRevision;', $this->script);
        self::assertStringContainsString("say('The Table stirred — the turn has changed.')", $this->script);
        self::assertStringContainsString('if (encounterLifecycleChanged)', $this->script);
    }

    public function testExistingHeartbeatRemainsFiveSeconds(): void
    {
        self::assertStringContainsString('window.setInterval(refresh, 5000)', $this->script);
    }
}
