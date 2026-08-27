<?php
declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tables\Scenes\Models;

use DateTimeImmutable;
use GreatMarketrealmTabletop\Tables\Scenes\Models\GridType;
use GreatMarketrealmTabletop\Tables\Scenes\Models\TableScene;
use PHPUnit\Framework\TestCase;

final class TableSceneCartographyTest extends TestCase
{
    public function testBattlemapMayChangeWithoutChangingGrid(): void
    {
        $scene = TableScene::create(
            'scene',
            'table',
            'Pantry',
            10,
            960,
            640,
            GridType::SQUARE,
            64,
            new DateTimeImmutable()
        );

        $scene->replaceMap(22, 2048, 1536);

        self::assertSame(22, $scene->mapAttachmentId());
        self::assertSame(2048, $scene->width());
        self::assertSame(1536, $scene->height());
        self::assertSame(GridType::SQUARE, $scene->gridType());
        self::assertSame(64, $scene->gridSize());
    }
}
