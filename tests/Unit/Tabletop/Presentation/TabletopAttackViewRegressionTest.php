<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Presentation;

use PHPUnit\Framework\TestCase;

final class TabletopAttackViewRegressionTest extends TestCase
{
    public function testChamberExposesAttackTargetSelector(): void
    {
        $source = (string) file_get_contents(
            dirname(__DIR__, 4)
                . '/app/Tabletop/Views/chamber.php'
        );

        self::assertStringContainsString(
            'data-attack-target',
            $source
        );
        self::assertStringContainsString(
            'Choose target',
            $source
        );
    }

    public function testClientUsesDedicatedAttackResolutionEndpoint(): void
    {
        $source = (string) file_get_contents(
            dirname(__DIR__, 4)
                . '/assets/js/tabletop.js'
        );

        self::assertStringContainsString(
            "request('gmrt_resolve_attack'",
            $source
        );
        self::assertStringContainsString(
            "attack.result === 'critical-hit'",
            $source
        );
        self::assertStringContainsString(
            "attack.result === 'critical-miss'",
            $source
        );
    }
}
