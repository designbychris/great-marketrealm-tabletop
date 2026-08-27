<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Battlefield\Services;

use DateTimeImmutable;
use GreatMarketrealmTabletop\Tabletop\Battlefield\Services\BattlefieldMeasure;
use GreatMarketrealmTabletop\Tables\Scenes\Models\GridType;
use GreatMarketrealmTabletop\Tables\Scenes\Models\TableScene;
use GreatMarketrealmTabletop\Tables\Tokens\Models\TableToken;
use GreatMarketrealmTabletop\Tables\Tokens\Models\TableTokenType;
use GreatMarketrealmTabletop\Tables\Tokens\Models\TableTokenVisibility;
use PHPUnit\Framework\TestCase;

final class BattlefieldMeasureTest extends TestCase
{
    public function testOneGridStepIsFiveFeet(): void
    {
        $distance = (new BattlefieldMeasure())->between(
            $this->scene(),
            $this->token('a', .20, .50),
            $this->token('b', .2666667, .50)
        );

        self::assertSame(1, $distance->squares());
        self::assertSame(5, $distance->feet());
        self::assertTrue($distance->adjacent());
    }

    public function testSquareGridDiagonalCountsAsOneStep(): void
    {
        $distance = (new BattlefieldMeasure())->between(
            $this->scene(),
            $this->token('a', .20, .20),
            $this->token('b', .2666667, .30)
        );

        self::assertSame(1, $distance->squares());
        self::assertSame(5, $distance->feet());
    }

    public function testLargerFootprintsMeasureFromNearestOccupiedSpaces(): void
    {
        $distance = (new BattlefieldMeasure())->between(
            $this->scene(),
            $this->token('large', .30, .50, 2, 2),
            $this->token('small', .50, .50)
        );

        self::assertSame(10, $distance->feet());
    }

    private function scene(): TableScene
    {
        $scene = TableScene::create(
            'scene-1',
            'table-1',
            'Training Yard',
            9,
            960,
            640,
            GridType::SQUARE,
            64,
            new DateTimeImmutable()
        );
        $scene->activate();

        return $scene;
    }

    private function token(
        string $id,
        float $x,
        float $y,
        float $width = 1,
        float $height = 1
    ): TableToken {
        return TableToken::create(
            $id,
            'table-1',
            'scene-1',
            $id,
            TableTokenType::CREATURE,
            'fixture:' . $id,
            null,
            $x,
            $y,
            $width,
            $height,
            TableTokenVisibility::VISIBLE,
            new DateTimeImmutable()
        );
    }
}
