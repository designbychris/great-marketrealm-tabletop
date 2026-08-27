<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Presentation;

use PHPUnit\Framework\TestCase;

final class PassTheTurnRegressionTest extends TestCase
{
    private string $view;
    private string $script;

    protected function setUp(): void
    {
        $root = dirname(__DIR__, 4);
        $this->view = (string) file_get_contents(
            $root . '/app/Tabletop/Views/chamber.php'
        );
        $this->script = (string) file_get_contents(
            $root . '/assets/js/tabletop.js'
        );
    }

    public function testActiveEncounterOffersEndTurnControl(): void
    {
        self::assertStringContainsString('data-end-turn', $this->view);
        self::assertStringContainsString('End Turn ▶', $this->view);
    }

    public function testEndTurnControlIsDungeonMasterOnly(): void
    {
        $dmCheck = strpos($this->view, '$state->isDungeonMaster()');
        $button = strpos($this->view, 'data-end-turn');

        self::assertIsInt($dmCheck);
        self::assertIsInt($button);
        self::assertLessThan($button, $dmCheck);
    }

    public function testTurnDisplaysCombatantLabelInsteadOfOpaqueId(): void
    {
        self::assertStringContainsString('$tokenLabels[$turnTokenId]', $this->view);
        self::assertStringContainsString('data-current-turn-label', $this->view);
    }

    public function testEndTurnUsesExistingAuthoritativeAdvanceEndpoint(): void
    {
        self::assertStringContainsString(
            "request('gmrt_advance_encounter'",
            $this->script
        );
        self::assertStringContainsString(
            'encounter_id: encounter.dataset.encounterId',
            $this->script
        );
    }

    public function testEndTurnSendsOptimisticEncounterRevision(): void
    {
        self::assertStringContainsString(
            'revision: encounter.dataset.encounterRevision',
            $this->script
        );
    }

    public function testSuccessfulPassRefreshesAuthoritativeStateInPlace(): void
    {
        self::assertStringContainsString(
            "say('Turn passed.');\n                await refresh();",
            $this->script
        );
    }
}
