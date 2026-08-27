<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Services;

use PHPUnit\Framework\TestCase;

final class TabletopBattleLogRegressionTest extends TestCase
{
    public function testChamberProjectsCurrentEncounterBattleEvents(): void
    {
        $source = (string) file_get_contents(
            dirname(__DIR__, 4)
                . '/app/Tabletop/Services/TabletopChamber.php'
        );

        self::assertStringContainsString(
            '$this->battleEvents->forEncounter(',
            $source
        );
        self::assertStringContainsString(
            '$this->battleLogProjector',
            $source
        );
    }

    public function testBattleLogOnlyUsesVisibleTokenLabels(): void
    {
        $source = (string) file_get_contents(
            dirname(__DIR__, 4)
                . '/app/Tabletop/Services/TabletopChamber.php'
        );

        $tokenProjection = strpos(
            $source,
            'foreach ($tokens as $token)'
        );
        $logProjection = strpos(
            $source,
            '$this->battleLogProjector'
        );

        self::assertIsInt($tokenProjection);
        self::assertIsInt($logProjection);
        self::assertLessThan(
            $logProjection,
            $tokenProjection
        );
    }

    public function testStateEndpointExposesBattleLog(): void
    {
        $source = (string) file_get_contents(
            dirname(__DIR__, 4)
                . '/app/Tabletop/Http/TabletopAjaxController.php'
        );

        self::assertStringContainsString(
            "'battle_log' => \$state->battleLog()",
            $source
        );
    }
}
