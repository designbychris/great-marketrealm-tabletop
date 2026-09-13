<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Presentation;

use PHPUnit\Framework\TestCase;

final class TabletopDeathAtDoorRegressionTest extends TestCase
{
    public function testCombatHudDistinguishesFallenAndStable(): void
    {
        $view = file_get_contents(
            dirname(__DIR__, 4)
            . '/app/Tabletop/Views/chamber.php'
        );
    
        self::assertIsString($view);
    
        self::assertStringContainsString(
            "'DECEASED' : 'DOWN'",
            $view
        );
    
        self::assertStringContainsString(
            "<span><?php esc_html_e('Death confirmed', 'great-marketrealm-tabletop'); ?></span>",
            $view
        );
    
        self::assertStringContainsString(
            "<span><?php esc_html_e('Stable', 'great-marketrealm-tabletop'); ?></span>",
            $view
        );
    
        self::assertStringNotContainsString(
            '>Fallen<',
            $view
        );
    }
}
