<?php
declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Fog\Services;

use GreatMarketrealmTabletop\Tables\Scenes\Models\TableScene;
use GreatMarketrealmTabletop\Tables\Tokens\Models\TableToken;
use GreatMarketrealmTabletop\Tabletop\Vision\Models\VisionBarrier;
use GreatMarketrealmTabletop\Tabletop\Vision\Services\SightLineResolver;

defined('ABSPATH') || exit;

final class FogCellMapper
{
    public const VISION_RADIUS = 3;

    /** @return array<int,string> */
    public function visibleAround(
        TableScene $scene,
        TableToken $token,
        array $barriers = [],
        int $visionRadius = self::VISION_RADIUS
    ): array {
        if ($scene->gridSize() < 1) {
            return [];
        }

        $visionRadius = max(1, min(60, $visionRadius));

        $center = $this->cellFor(
            $scene,
            $token->x(),
            $token->y()
        );

        $cells = [];

        for (
            $row = $center['row'] - $visionRadius;
            $row <= $center['row'] + $visionRadius;
            ++$row
        ) {
            for (
                $column = $center['column'] - $visionRadius;
                $column <= $center['column'] + $visionRadius;
                ++$column
            ) {
                if (
                    max(
                        abs($column - $center['column']),
                        abs($row - $center['row'])
                    ) > $visionRadius
                ) {
                    continue;
                }

                if (! (new SightLineResolver())->canSee(
                    $center['column'],
                    $center['row'],
                    $column,
                    $row,
                    $barriers
                )) {
                    continue;
                }

                $cells[] = self::key(
                    $column,
                    $row
                );
            }
        }

        return array_values(array_unique($cells));
    }

    /** @return array{column:int,row:int} */
    public function cellFor(
        TableScene $scene,
        float $x,
        float $y
    ): array {
        $pixelX = $x * $scene->width();
        $pixelY = $y * $scene->height();

        $referenceWidth = $scene->gridReferenceWidth();
        $scale = $referenceWidth > 0
            ? $scene->width() / $referenceWidth
            : 1.0;

        $size = max(
            1.0,
            $scene->gridSize() * $scale
        );
        $offsetX = $scene->gridOffsetX() * $scale;
        $offsetY = $scene->gridOffsetY() * $scale;

        return [
            'column' => (int) floor(
                ($pixelX - $offsetX)
                / $size
            ),
            'row' => (int) floor(
                ($pixelY - $offsetY)
                / $size
            ),
        ];
    }

    public static function key(
        int $column,
        int $row
    ): string {
        return $column . ':' . $row;
    }
}
