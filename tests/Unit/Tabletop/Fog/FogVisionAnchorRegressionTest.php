<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Fog;

use PHPUnit\Framework\TestCase;

final class FogVisionAnchorRegressionTest extends TestCase
{
    public function testProjectionExposesServerCharacterVisionOrigins(): void
    {
        $source = (string) file_get_contents(
            dirname(__DIR__, 4)
            . '/app/Tabletop/Fog/Services/FogOfWarProjector.php'
        );

        self::assertStringContainsString("'vision_origins' => \$visionOrigins", $source);
        self::assertStringContainsString("'x' => \$token->x()", $source);
        self::assertStringContainsString("'y' => \$token->y()", $source);
    }

    public function testLivingVeilAnchorsVisibleCellsToRenderedVisionOrigins(): void
    {
        $source = (string) file_get_contents(
            dirname(__DIR__, 4) . '/assets/js/tabletop.js'
        );

        self::assertStringContainsString('fogProjection.vision_origins', $source);
        self::assertStringContainsString('visible.add(`${column}:${row}`)', $source);
        self::assertStringContainsString('x * width', $source);
        self::assertStringContainsString('y * height', $source);
    }
}
