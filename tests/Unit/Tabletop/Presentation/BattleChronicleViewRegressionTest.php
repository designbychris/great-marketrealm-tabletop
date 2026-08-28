<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Presentation;

use PHPUnit\Framework\TestCase;

final class BattleChronicleViewRegressionTest extends TestCase
{
    public function testChamberContainsBattleChronicle(): void
    {
        $view = (string) file_get_contents(
            dirname(__DIR__, 4)
                . '/app/Tabletop/Views/chamber.php'
        );

        self::assertStringContainsString(
            'Battle Chronicle',
            $view
        );
        self::assertStringContainsString(
            'data-battle-log',
            $view
        );
    }

    public function testClientRefreshesChronicleFromState(): void
    {
        $script = (string) file_get_contents(
            dirname(__DIR__, 4)
                . '/assets/js/tabletop.js'
        );

        self::assertStringContainsString(
            'renderChronicle(state.encounter ? state.battle_log : state.chamber_log, Boolean(state.encounter))',
            $script
        );
        self::assertStringContainsString(
            'document.createElement',
            $script
        );
    }

    public function testAttackDetailMovesIntoDiceworksOutcome(): void
    {
        $view = (string) file_get_contents(
            dirname(__DIR__, 4)
                . '/app/Tabletop/Views/chamber.php'
        );
        $script = (string) file_get_contents(
            dirname(__DIR__, 4)
                . '/assets/js/tabletop.js'
        );

        self::assertStringContainsString(
            'data-diceworks-outcome',
            $view
        );
        self::assertStringContainsString(
            'renderImmediateCombatOutcome(data)',
            $script
        );
        self::assertStringContainsString(
            "'Attack resolved — see Guild Diceworks.'",
            $script
        );
    }

    public function testImmediateOutcomeIncludesDamageAndHp(): void
    {
        $script = (string) file_get_contents(
            dirname(__DIR__, 4)
                . '/assets/js/tabletop.js'
        );

        self::assertStringContainsString(
            'adjusted.resolved_damage',
            $script
        );
        self::assertStringContainsString(
            'data.vitality.current_hp',
            $script
        );
        self::assertStringContainsString(
            "' · WEAK!'",
            $script
        );
        self::assertStringContainsString(
            "' · IMMUNE!'",
            $script
        );
        self::assertStringContainsString(
            "' · RESIST!'",
            $script
        );
    }
}
