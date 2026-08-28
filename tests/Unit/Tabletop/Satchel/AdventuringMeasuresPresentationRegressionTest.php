<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Satchel;

use PHPUnit\Framework\TestCase;

final class AdventuringMeasuresPresentationRegressionTest extends TestCase
{
    public function testSatchelExposesMutableCurrentAndTemporaryHpButNotMaximumHpInput(): void
    {
        $view = (string) file_get_contents(dirname(__DIR__, 4) . '/app/Tabletop/Views/chamber.php');
        self::assertStringContainsString('data-adventuring-measures-form', $view);
        self::assertStringContainsString('name="current_hp"', $view);
        self::assertStringContainsString('name="temporary_hp"', $view);
        self::assertStringNotContainsString('name="maximum_hp"', $view);
        self::assertStringContainsString('Companion-certified', $view);
    }

    public function testBrowserSubmitsOnlyMutableMeasuresToDedicatedEndpoint(): void
    {
        $script = (string) file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertStringContainsString("gmrt_update_adventuring_measures", $script);
        self::assertStringContainsString('current_hp:', $script);
        self::assertStringContainsString('temporary_hp:', $script);
        self::assertStringNotContainsString('maximum_hp:', $script);
    }
}
