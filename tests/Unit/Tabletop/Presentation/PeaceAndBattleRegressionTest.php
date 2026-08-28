<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Presentation;

use PHPUnit\Framework\TestCase;

final class PeaceAndBattleRegressionTest extends TestCase
{
    private string $view;
    private string $script;
    private string $provider;
    private string $manager;

    protected function setUp(): void
    {
        $root = dirname(__DIR__, 4);
        $this->view = (string) file_get_contents($root . '/app/Tabletop/Views/chamber.php');
        $this->script = (string) file_get_contents($root . '/assets/js/tabletop.js');
        $this->provider = (string) file_get_contents($root . '/app/Tabletop/TabletopServiceProvider.php');
        $this->manager = (string) file_get_contents($root . '/app/Tabletop/Encounters/Services/EncounterManager.php');
    }

    public function testExplorationModeExistsWithoutEncounter(): void
    {
        self::assertStringContainsString('$encounter === null', $this->view);
        self::assertStringContainsString('Exploration Mode', $this->view);
        self::assertStringContainsString('data-exploration-mode', $this->view);
    }

    public function testDungeonMasterCanPrepareBattleFromExploration(): void
    {
        self::assertStringContainsString('data-start-encounter', $this->view);
        self::assertStringContainsString('data-encounter-combatant', $this->view);
        self::assertStringContainsString('data-encounter-initiative', $this->view);
        self::assertStringContainsString("request('gmrt_begin_encounter'", $this->script);
    }

    public function testBeginEncounterHasDedicatedAuthenticatedAjaxRoute(): void
    {
        self::assertStringContainsString("'wp_ajax_gmrt_begin_encounter'", $this->provider);
        self::assertStringContainsString("[\$this->encounterAjax, 'begin']", $this->provider);
    }

    public function testBeginEncounterIsServerAuthoritativeAndAtomic(): void
    {
        self::assertStringContainsString('public function begin(', $this->manager);
        self::assertStringContainsString('$this->requireDungeonMaster($tableId, $viewerUserId);', $this->manager);
        self::assertStringContainsString('$encounter->start();', $this->manager);
        self::assertStringContainsString('$this->encounters->save($encounter);', $this->manager);
    }

    public function testDungeonMasterCanEndEncounter(): void
    {
        self::assertStringContainsString('data-end-encounter', $this->view);
        self::assertStringContainsString("request('gmrt_end_encounter'", $this->script);
    }

    public function testLifecycleTransitionPatchesOnlyLiveRegions(): void
    {
        self::assertStringContainsString('async function replaceLifecycle(message)', $this->script);
        self::assertStringContainsString("request('gmrt_tabletop_fragment', {})", $this->script);
        self::assertStringContainsString('currentLifecycle.replaceChildren', $this->script);
        self::assertStringContainsString('await replaceLifecycle(', $this->script);
        self::assertStringNotContainsString('await replaceChamber(', $this->script);
    }

    public function testHeartbeatUsesLiveLifecycleTransition(): void
    {
        self::assertStringContainsString('if (encounterLifecycleChanged)', $this->script);
        self::assertStringContainsString("? 'Battle has begun — the Table takes its places.'", $this->script);
        self::assertStringContainsString(": 'Peace returns — exploration resumes.'", $this->script);
    }

    public function testExplorationCopyProtectsSceneSystems(): void
    {
        self::assertStringContainsString('Living Veil, doors, walls', $this->view);
        self::assertStringContainsString('remembered route remain active', $this->view);
    }
}
