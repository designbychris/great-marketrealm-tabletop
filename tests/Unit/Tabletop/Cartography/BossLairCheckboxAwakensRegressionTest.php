<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class BossLairCheckboxAwakensRegressionTest extends TestCase
{
    private function javascript(): string
    {
        return (string) file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
    }

    public function test_behind_the_curtain_lair_control_is_declared_before_its_availability_check_runs(): void
    {
        $js = $this->javascript();
        $declaration = strpos($js, "const dungeonForgeLair = document.querySelector('[data-dungeon-forge-lair]');");
        $initialisation = strpos($js, 'updateDungeonForgeLairAvailability();');

        self::assertNotFalse($declaration);
        self::assertNotFalse($initialisation);
        self::assertLessThan($initialisation, $declaration);
    }

    public function test_availability_check_enables_only_grand_dungeons(): void
    {
        $js = $this->javascript();
        self::assertStringContainsString("String(dungeonForgeSceneType?.value || 'dungeon') === 'dungeon'", $js);
        self::assertStringContainsString("String(dungeonForgeStyle?.value || 'standard') === 'grand'", $js);
        self::assertStringContainsString('dungeonForgeLair.disabled = !allowed;', $js);
    }

    public function test_atlas_lair_control_still_uses_the_same_grand_dungeon_gate(): void
    {
        $js = $this->javascript();
        self::assertStringContainsString("String(atlasForgeSceneType?.value || 'dungeon') === 'dungeon'", $js);
        self::assertStringContainsString("String(atlasForgeStyle?.value || 'standard') === 'grand'", $js);
        self::assertStringContainsString('atlasForgeLair.disabled = !allowed;', $js);
    }

    public function test_disallowed_modes_clear_a_previous_lair_choice(): void
    {
        $js = $this->javascript();
        self::assertStringContainsString('if (!allowed) dungeonForgeLair.checked = false;', $js);
        self::assertStringContainsString('if (!allowed) atlasForgeLair.checked = false;', $js);
    }
}
