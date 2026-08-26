<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tables\Scenes\Models;

use DateTimeImmutable;
use GreatMarketrealmTabletop\Tables\Scenes\Models\GridType;
use GreatMarketrealmTabletop\Tables\Scenes\Models\TableScene;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class TableSceneTest extends TestCase
{
    public function testBattlemapSurfaceIsPersistentAndTokenReady(): void
    {
        $scene = TableScene::create(
            'scene-1', 'table-1', 'The Cold Vault', 42,
            1920, 1080, GridType::SQUARE, 70,
            new DateTimeImmutable('2026-08-26T10:00:00+01:00')
        );

        self::assertSame('scene-1', $scene->id());
        self::assertSame('table-1', $scene->tableId());
        self::assertSame('The Cold Vault', $scene->name());
        self::assertSame(42, $scene->mapAttachmentId());
        self::assertSame(1920, $scene->width());
        self::assertSame(1080, $scene->height());
        self::assertSame('square', $scene->gridType());
        self::assertSame(70, $scene->gridSize());
        self::assertFalse($scene->isActive());
        self::assertSame(['x' => .25, 'y' => .75], $scene->coordinates(.25, .75));
    }

    public function testGridlessSurfaceUsesZeroGridSize(): void
    {
        $scene = TableScene::create(
            'scene-1', 'table-1', 'Market Square', 7,
            1000, 1000, GridType::NONE, 0,
            new DateTimeImmutable()
        );

        self::assertSame('none', $scene->gridType());
        self::assertSame(0, $scene->gridSize());
    }

    public function testInvalidNormalisedCoordinatesAreRejected(): void
    {
        $scene = TableScene::create(
            'scene-1', 'table-1', 'Map', 1, 100, 100,
            GridType::NONE, 0, new DateTimeImmutable()
        );

        $this->expectException(InvalidArgumentException::class);
        $scene->coordinates(1.01, .5);
    }

    public function testSquareGridRequiresPositiveSize(): void
    {
        $this->expectException(InvalidArgumentException::class);
        TableScene::create(
            'scene-1', 'table-1', 'Map', 1, 100, 100,
            GridType::SQUARE, 0, new DateTimeImmutable()
        );
    }

    public function testSceneRoundTripsThroughRecord(): void
    {
        $scene = TableScene::create(
            'scene-1', 'table-1', 'Map', 9, 800, 600,
            GridType::SQUARE, 50, new DateTimeImmutable('2026-08-26T10:00:00+01:00')
        );
        $scene->activate();

        $copy = TableScene::reconstitute($scene->toArray());
        self::assertSame($scene->toArray(), $copy->toArray());
        self::assertTrue($copy->isActive());
    }
}
