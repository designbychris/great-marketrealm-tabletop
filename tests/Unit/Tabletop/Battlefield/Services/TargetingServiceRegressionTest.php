<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Battlefield\Services;

use PHPUnit\Framework\TestCase;

final class TargetingServiceRegressionTest extends TestCase
{
    public function testPreviewUsesAuthoritativeBattlefieldAndRangeServices(): void
    {
        $source = (string) file_get_contents(
            dirname(__DIR__, 5)
                . '/app/Tabletop/Battlefield/Services/TargetingService.php'
        );

        self::assertStringContainsString(
            '$this->battlefield->between(',
            $source
        );
        self::assertStringContainsString(
            '$this->ranges->assess(',
            $source
        );
    }

    public function testPreviewIncludesRollModeForDiceworks(): void
    {
        $source = (string) file_get_contents(
            dirname(__DIR__, 5)
                . '/app/Tabletop/Battlefield/Services/TargetingService.php'
        );

        self::assertStringContainsString(
            "'roll_mode' => \$rollMode",
            $source
        );
    }
}
