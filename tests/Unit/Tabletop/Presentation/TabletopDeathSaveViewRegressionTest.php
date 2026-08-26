<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Presentation;

use PHPUnit\Framework\TestCase;

final class TabletopDeathSaveViewRegressionTest extends TestCase
{
    public function testDownedCombatantGetsDeathSaveHud(): void
    {
        $source = (string) file_get_contents(
            dirname(__DIR__, 4) . '/app/Tabletop/Views/chamber.php'
        );

        self::assertStringContainsString('data-death-saves', $source);
        self::assertStringContainsString('data-roll-death-save', $source);
        self::assertStringContainsString('Roll Death Save', $source);
    }

    public function testClientHandlesNaturalOneAndTwenty(): void
    {
        $source = (string) file_get_contents(
            dirname(__DIR__, 4) . '/assets/js/tabletop.js'
        );

        self::assertStringContainsString(
            "save.result === 'natural-twenty'",
            $source
        );
        self::assertStringContainsString(
            "save.result === 'natural-one'",
            $source
        );
        self::assertStringContainsString(
            "request('gmrt_roll_death_save'",
            $source
        );
    }
}
