<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Presentation;

use PHPUnit\Framework\TestCase;

final class TabletopVitalityViewRegressionTest extends TestCase
{
    public function testPartyHudExposesHitPointBarMarkup(): void
    {
        $source = (string) file_get_contents(
            dirname(__DIR__, 4)
                . '/app/Tabletop/Views/chamber.php'
        );

        self::assertStringContainsString(
            'gmrt-hp__track',
            $source
        );
        self::assertStringContainsString(
            'gmrt-hp__fill',
            $source
        );
        self::assertStringContainsString(
            'temporary_hp',
            $source
        );
    }

    public function testAttackClientAnnouncesDamageAndRemainingHp(): void
    {
        $source = (string) file_get_contents(
            dirname(__DIR__, 4)
                . '/assets/js/tabletop.js'
        );

        self::assertStringContainsString(
            'adjusted.resolved_damage',
            $source
        );
        self::assertStringContainsString(
            'adjusted.raw_damage',
            $source
        );
        self::assertStringContainsString(
            "data.vitality.current_hp",
            $source
        );
    }
}
