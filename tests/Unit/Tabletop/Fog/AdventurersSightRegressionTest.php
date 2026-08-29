<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Fog;

use PHPUnit\Framework\TestCase;

final class AdventurersSightRegressionTest extends TestCase
{
    public function testFogMapperAcceptsAViewerSpecificVisionRadius(): void
    {
        $mapper = file_get_contents(dirname(__DIR__, 4) . '/app/Tabletop/Fog/Services/FogCellMapper.php');
        self::assertIsString($mapper);
        self::assertStringContainsString('int $visionRadius = self::VISION_RADIUS', $mapper);
        self::assertStringContainsString('$visionRadius = max(1, min(60, $visionRadius))', $mapper);
    }

    public function testFogProjectionUsesCompanionDarkvisionInFiveFootSquares(): void
    {
        $projector = file_get_contents(dirname(__DIR__, 4) . '/app/Tabletop/Fog/Services/FogOfWarProjector.php');
        self::assertIsString($projector);
        self::assertStringContainsString("\$profile['darkvision']", $projector);
        self::assertStringContainsString('ceil($visionFeet / 5)', $projector);
        self::assertStringContainsString("'range_feet' => \$visionFeet", $projector);
    }

    public function testPlayersOnlyProjectSightFromTheirOwnControlledCharacter(): void
    {
        $chamber = file_get_contents(dirname(__DIR__, 4) . '/app/Tabletop/Services/TabletopChamber.php');
        self::assertIsString($chamber);
        self::assertStringContainsString('$token->controllerUserId() === $viewerUserId', $chamber);
        self::assertStringContainsString("\$character['play']['senses']", $chamber);
        self::assertStringContainsString("'darkvision' => max(0", $chamber);
    }
}
