<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Presentation;

use PHPUnit\Framework\TestCase;

final class TabletopDeedsViewRegressionTest extends TestCase
{
    public function testActiveEncounterExposesFirstFiveDeedControls(): void
    {
        $source = (string) file_get_contents(
            dirname(__DIR__, 4)
                . '/app/Tabletop/Views/chamber.php'
        );

        foreach (
            ['attack', 'dash', 'disengage', 'dodge', 'help']
            as $deed
        ) {
            self::assertStringContainsString(
                "data-battle-deed=\"<?php echo esc_attr(",
                $source
            );
        }

        self::assertStringContainsString(
            'Battle deeds',
            $source
        );
    }

    public function testClientPostsBattleDeedAndUpdatesEncounterRevision(): void
    {
        $source = (string) file_get_contents(
            dirname(__DIR__, 4)
                . '/assets/js/tabletop.js'
        );

        self::assertStringContainsString(
            "request('gmrt_perform_battle_deed'",
            $source
        );
        self::assertStringContainsString(
            'encounter.dataset.encounterRevision',
            $source
        );
    }
}
