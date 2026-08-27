<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Presentation;

use PHPUnit\Framework\TestCase;

final class CombatDiceworksRegressionTest extends TestCase
{
    public function testChamberContainsGuildDiceworksTray(): void
    {
        $view = (string) file_get_contents(
            dirname(__DIR__, 4)
                . '/app/Tabletop/Views/chamber.php'
        );

        self::assertStringContainsString(
            'Guild Diceworks',
            $view
        );
        self::assertSame(
            2,
            substr_count($view, 'data-combat-die=')
        );
    }

    public function testAdvantageAndDisadvantageRevealBothCertifiedRolls(): void
    {
        $script = (string) file_get_contents(
            dirname(__DIR__, 4)
                . '/assets/js/tabletop.js'
        );

        self::assertStringContainsString(
            'Array.isArray(attack.rolls)',
            $script
        );
        self::assertStringContainsString(
            'value.textContent = String(rolls[index])',
            $script
        );
        self::assertStringContainsString(
            "'is-rejected'",
            $script
        );
        self::assertStringContainsString(
            "'is-chosen'",
            $script
        );
    }

    public function testNaturalOneRetainsLonelyConfettiTechnology(): void
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
            'data-lonely-confetti',
            $view
        );
        self::assertStringContainsString(
            "attack.result !== 'critical-miss'",
            $script
        );
    }

    public function testDiceAnimationRespectsReducedMotion(): void
    {
        $css = (string) file_get_contents(
            dirname(__DIR__, 4)
                . '/assets/css/tabletop.css'
        );

        self::assertStringContainsString(
            '@media (prefers-reduced-motion: reduce)',
            $css
        );
        self::assertStringContainsString(
            'gmrt-d20-tumble',
            $css
        );
    }
}
