<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Presentation;

use PHPUnit\Framework\TestCase;

final class TabletopReadabilityRegressionTest extends TestCase
{
    public function test_keeper_operational_copy_has_a_stronger_readability_floor(): void
    {
        $css = file_get_contents(dirname(__DIR__, 4) . '/assets/css/tabletop.css');
        self::assertIsString($css);
        self::assertStringContainsString('The Keeper Finds His Reading Glasses', $css);
        self::assertStringContainsString('.gmrt-chamber {', $css);
        self::assertStringContainsString('font-size: 16px;', $css);
        self::assertStringContainsString('font-size: .82rem;', $css);
        self::assertStringContainsString('.gmrt-chamber :where(input, select, textarea)', $css);
    }
}
