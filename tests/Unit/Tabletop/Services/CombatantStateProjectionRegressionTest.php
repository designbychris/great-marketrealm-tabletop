<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Services;

use PHPUnit\Framework\TestCase;

final class CombatantStateProjectionRegressionTest extends TestCase
{
    public function testChamberProjectsStatesFromVitalityAndDeathSaves(): void
    {
        $source = (string) file_get_contents(
            dirname(__DIR__, 4)
                . '/app/Tabletop/Services/TabletopChamber.php'
        );

        self::assertStringContainsString(
            '$this->combatantStateProjector->project(',
            $source
        );
        self::assertStringContainsString(
            '$vitality[$tokenId]',
            $source
        );
        self::assertStringContainsString(
            '$deathSaves[$tokenId]',
            $source
        );
    }

    public function testStateEndpointExposesCombatantStates(): void
    {
        $source = (string) file_get_contents(
            dirname(__DIR__, 4)
                . '/app/Tabletop/Http/TabletopAjaxController.php'
        );

        self::assertStringContainsString(
            "'combatant_states' => \$state->combatantStates()",
            $source
        );
    }
}
