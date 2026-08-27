<?php
declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Fog\Services;

use GreatMarketrealmTabletop\Tables\Scenes\Models\TableScene;
use GreatMarketrealmTabletop\Tables\Tokens\Models\TableToken;
use GreatMarketrealmTabletop\Tables\Tokens\Models\TableTokenType;
use GreatMarketrealmTabletop\Tabletop\Fog\Models\FogOfWarState;

defined('ABSPATH') || exit;

final class FogOfWarProjector
{
    /**
     * @param array<int,TableToken> $tokens
     * @return array<string,mixed>
     */
    public function project(
        TableScene $scene,
        FogOfWarState $fog,
        array $tokens,
        bool $dungeonMaster,
        array $barriers = []
    ): array {
        $visible = [];
        $visionOrigins = [];

        foreach ($tokens as $token) {
            if (
                ! $token instanceof TableToken
                || $token->type() !== TableTokenType::CHARACTER
            ) {
                continue;
            }

            $visionOrigins[] = [
                'x' => $token->x(),
                'y' => $token->y(),
            ];

            $visible = array_merge(
                $visible,
                (new FogCellMapper())->visibleAround(
                    $scene,
                    $token,
                    $barriers
                )
            );
        }

        return [
            'enabled' => $fog->enabled(),
            'bypass' => $dungeonMaster,
            'vision_radius' => FogCellMapper::VISION_RADIUS,
            'grid_size' => $scene->gridSize(),
            'offset_x' => $scene->gridOffsetX(),
            'offset_y' => $scene->gridOffsetY(),
            'reference_width' => $scene->gridReferenceWidth(),
            'width' => $scene->width(),
            'height' => $scene->height(),
            'explored' => array_values(array_unique(
                $fog->explored()
            )),
            'visible' => array_values(array_unique(
                $visible
            )),
            'vision_origins' => $visionOrigins,
            'has_blockers' => $barriers !== [],
        ];
    }

    /** @param array<string,mixed> $projection */
    public function tokenIsCurrentlyVisible(
        TableScene $scene,
        TableToken $token,
        array $projection
    ): bool {
        if (
            empty($projection['enabled'])
            || ! empty($projection['bypass'])
            || $token->type() === TableTokenType::CHARACTER
        ) {
            return true;
        }

        $cell = (new FogCellMapper())->cellFor(
            $scene,
            $token->x(),
            $token->y()
        );

        return in_array(
            FogCellMapper::key(
                $cell['column'],
                $cell['row']
            ),
            $projection['visible'] ?? [],
            true
        );
    }
}
