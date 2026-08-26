<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Presentation;

use PHPUnit\Framework\TestCase;

final class TabletopEncounterViewRegressionTest extends TestCase
{
    public function testChamberHasMinimalBattleProjectionForFutureHud(): void
    {
        $source = (string) file_get_contents(
            dirname(__DIR__, 4)
                . '/app/Tabletop/Views/chamber.php'
        );

        self::assertStringContainsString('The Turn of Battle', $source);
        self::assertStringContainsString('Round ', $source);
        self::assertStringContainsString('current_token_id', $source);
        self::assertStringContainsString('data-encounter-revision', $source);
    }

    public function testCurrentHudDoesNotEmbedPixelCharacterAssetsYet(): void
    {
        $source = strtolower(
            (string) file_get_contents(
                dirname(__DIR__, 4)
                    . '/app/Tabletop/Views/chamber.php'
            )
        );

        self::assertStringNotContainsString('pixelauby', $source);
        self::assertStringNotContainsString('pixelsage', $source);
    }
}
