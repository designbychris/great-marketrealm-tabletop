<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Battlefield\Services;

use GreatMarketrealmTabletop\Tabletop\Battlefield\Models\BattlefieldDistance;
use GreatMarketrealmTabletop\Tables\Scenes\Models\GridType;
use GreatMarketrealmTabletop\Tables\Scenes\Models\TableScene;
use GreatMarketrealmTabletop\Tables\Tokens\Models\TableToken;
use RuntimeException;

defined('ABSPATH') || exit;

final class BattlefieldMeasure
{
    private const FEET_PER_SQUARE = 5;

    public function between(
        TableScene $scene,
        TableToken $first,
        TableToken $second
    ): BattlefieldDistance {
        if (
            $first->sceneId() !== $scene->id()
            || $second->sceneId() !== $scene->id()
        ) {
            throw new RuntimeException(
                'Battlefield distance requires tokens on the measured Scene.'
            );
        }

        if (
            $scene->gridType() !== GridType::SQUARE
            || $scene->gridSize() < 1
        ) {
            throw new RuntimeException(
                'IV.18 battlefield distance currently requires a square grid.'
            );
        }

        $gridWidth = $scene->width()
            / $scene->gridSize();
        $gridHeight = $scene->height()
            / $scene->gridSize();

        $deltaX = abs(
            ($first->x() - $second->x())
            * $gridWidth
        );
        $deltaY = abs(
            ($first->y() - $second->y())
            * $gridHeight
        );

        $footprintX = max(
            0,
            ($first->widthUnits() - 1) / 2
            + ($second->widthUnits() - 1) / 2
        );
        $footprintY = max(
            0,
            ($first->heightUnits() - 1) / 2
            + ($second->heightUnits() - 1) / 2
        );

        $nearestX = max(
            0,
            $deltaX - $footprintX
        );
        $nearestY = max(
            0,
            $deltaY - $footprintY
        );

        // Fifth-edition square-grid diagonals count as one square.
        $squares = (int) ceil(
            max($nearestX, $nearestY)
            - 0.000001
        );

        return new BattlefieldDistance(
            max(0, $squares),
            max(0, $squares)
                * self::FEET_PER_SQUARE
        );
    }
}
