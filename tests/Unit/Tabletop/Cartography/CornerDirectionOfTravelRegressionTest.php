<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class CornerDirectionOfTravelRegressionTest extends TestCase
{
    public function test_directional_witness_is_bounded_and_diagnostic_only(): void
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertIsString($source);
        self::assertStringContainsString("IV.30.1G.5Z.50Z.12 — The Corner's Direction of Travel", $source);
        self::assertStringContainsString('illustratedPropagationBarrierCornerDirectionOfTravelReviews,', $source);
        self::assertStringContainsString('illustratedPropagationBarrierCornerDirectionOfTravelSamples,', $source);
        self::assertStringContainsString('cornerTurnDemonstrated:false,structuralHandshake:\'unproven\'', $source);
        self::assertStringContainsString('diagnostic-only;bounded-original-image-directional-travel;no-snapping;no-wall-admission;no-recovery-replay', $source);
        self::assertStringContainsString('for(let i=-4;i<=4;i++)', $source);
        self::assertStringContainsString('for(let j=-4;j<=4;j++)', $source);
    }
}
