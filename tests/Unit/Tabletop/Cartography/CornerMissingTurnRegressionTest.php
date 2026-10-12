<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class CornerMissingTurnRegressionTest extends TestCase
{
    public function test_missing_turn_audit_remains_bounded_and_non_admitting(): void
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertIsString($source);
        self::assertStringContainsString("IV.30.1G.5Z.50Z.13 — The Corner's Missing Turn", $source);
        self::assertStringContainsString('illustratedPropagationBarrierCornerMissingTurnReviews,', $source);
        self::assertStringContainsString('illustratedPropagationBarrierCornerMissingTurnSamples,', $source);
        self::assertStringContainsString('four-neighbour connected within the same dark component', $source);
        self::assertStringContainsString("structuralPartner:'unproven',structuralHandshake:'unproven'", $source);
        self::assertStringContainsString('diagnostic-only;bounded-four-connected-original-image-pixel-turn;no-snapping;no-wall-admission;no-recovery-replay', $source);
        self::assertStringContainsString('for(let i=-4;i<=4;i++)', $source);
        self::assertStringContainsString('for(let j=-4;j<=4;j++)', $source);
    }
}
