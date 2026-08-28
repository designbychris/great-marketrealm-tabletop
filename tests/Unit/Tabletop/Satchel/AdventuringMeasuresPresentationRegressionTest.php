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

    public function testGatheringUsesCompanionAuthoritativeHpAndSatchelHasRoomToBreathe(): void
    {
        $root = dirname(__DIR__, 4);
        $view = (string) file_get_contents($root . '/app/Tabletop/Views/chamber.php');
        $chamber = (string) file_get_contents($root . '/app/Tabletop/Services/TabletopChamber.php');
        $css = (string) file_get_contents($root . '/assets/css/tabletop.css');
        $script = (string) file_get_contents($root . '/assets/js/tabletop.js');

        self::assertStringContainsString("['companion_character'] = \$this->companion->characterForUser", $chamber);
        self::assertStringContainsString("['hit_points']", $view);
        self::assertStringContainsString('data-party-character-hp', $view);
        self::assertStringContainsString('data-party-current-hp', $view);
        self::assertStringContainsString('width:min(460px,calc(100vw - 4rem))', $css);
        self::assertStringContainsString('overflow-y:auto;overflow-x:hidden', $css);
        self::assertStringContainsString('data-party-character-hp', $script);
    }
}
