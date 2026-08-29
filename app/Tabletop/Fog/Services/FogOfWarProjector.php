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
        array $barriers = [],
        array $visionProfiles = [],
        array $worldLightSources = []
    ): array {
        $visible = [];
        $visionOrigins = [];
        $viewerLineOfSight = [];

        foreach ($tokens as $token) {
            if (
                ! $token instanceof TableToken
                || $token->type() !== TableTokenType::CHARACTER
            ) {
                continue;
            }

            $profile = is_array($visionProfiles[$token->id()] ?? null)
                ? $visionProfiles[$token->id()]
                : [];
            $darkvisionFeet = max(0, (int) ($profile['darkvision'] ?? 0));
            $lightFeet = !empty($profile['carried_light']) ? max(0, (int) ($profile['light_radius_feet'] ?? 40)) : 0;
            $visionFeet = max(15, $darkvisionFeet, $lightFeet);
            $visionRadius = max(1, (int) ceil($visionFeet / 5));

            $visionOrigins[] = [
                'x' => $token->x(),
                'y' => $token->y(),
                'token_id' => $token->id(),
                'range_feet' => $visionFeet,
                'darkvision' => $darkvisionFeet,
                'carried_light' => $lightFeet > 0,
                'light_radius_feet' => $lightFeet,
                'bright_light_feet' => $lightFeet > 0 ? 20 : 0,
                'dim_light_feet' => $lightFeet > 0 ? 20 : 0,
            ];

            $mapper = new FogCellMapper();
            $visible = array_merge(
                $visible,
                $mapper->visibleAround(
                    $scene,
                    $token,
                    $barriers,
                    $visionRadius
                )
            );

            // Shared illumination may be seen beyond the adventurer's natural
            // sight radius, but only where the viewer has an unobstructed line
            // of sight. This remains server-authoritative.
            $viewerLineOfSight = array_merge(
                $viewerLineOfSight,
                $mapper->visibleAround($scene, $token, $barriers, 60)
            );
        }

        $visible = array_values(array_unique($visible));
        $viewerLineOfSight = array_values(array_unique($viewerLineOfSight));
        $safeLightSources = [];

        foreach ($worldLightSources as $lightSource) {
            if (! $lightSource instanceof TableToken || $lightSource->type() !== TableTokenType::CHARACTER) {
                continue;
            }

            $mapper = new FogCellMapper();
            $illuminated = $mapper->visibleAround($scene, $lightSource, $barriers, 8);
            $sharedVisible = array_values(array_intersect($illuminated, $viewerLineOfSight));
            $visible = array_merge($visible, $sharedVisible);

            $sourceCell = $mapper->cellFor($scene, $lightSource->x(), $lightSource->y());
            $sourceKey = FogCellMapper::key($sourceCell['column'], $sourceCell['row']);
            if ($dungeonMaster || in_array($sourceKey, $visible, true)) {
                $safeLightSources[] = [
                    'x' => $lightSource->x(),
                    'y' => $lightSource->y(),
                    'token_id' => $lightSource->id(),
                    'range_feet' => 40,
                    'bright_light_feet' => 20,
                    'dim_light_feet' => 20,
                    'shared' => true,
                ];
            }
        }

        $visible = array_values(array_unique($visible));
        $ownLightSources = array_values(array_filter($visionOrigins, static fn (array $origin): bool => !empty($origin['carried_light'])));
        $lightSources = [];
        foreach (array_merge($ownLightSources, $safeLightSources) as $source) {
            $lightSources[(string) ($source['token_id'] ?? '')] = $source;
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
            'visible' => $visible,
            'vision_origins' => $visionOrigins,
            'light_sources' => array_values($lightSources),
            'viewer_carried_light' => $ownLightSources !== [],
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
