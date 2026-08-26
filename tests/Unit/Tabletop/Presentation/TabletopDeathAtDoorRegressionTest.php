<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Presentation;

use PHPUnit\Framework\TestCase;

final class TabletopDeathAtDoorRegressionTest extends TestCase
{
    public function testCombatHudDistinguishesFallenAndStable(): void
    {
        $source = (string) file_get_contents(
            dirname(__DIR__, 4)
                . '/app/Tabletop/Views/chamber.php'
        );

        self::assertStringContainsString('>Fallen<', $source);
        self::assertStringContainsString('>Stable<', $source);
    }
}
