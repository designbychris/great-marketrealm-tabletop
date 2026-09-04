<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Fog\Services;

use GreatMarketrealmTabletop\Tables\Scenes\Models\TableScene;
use GreatMarketrealmTabletop\Tables\Tokens\Models\TableToken;
use GreatMarketrealmTabletop\Tables\Tokens\Models\TableTokenType;
use GreatMarketrealmTabletop\Tabletop\Fog\Models\FogOfWarState;
use GreatMarketrealmTabletop\Tabletop\Light\Models\DroppedLight;
use GreatMarketrealmTabletop\Tabletop\Light\Models\EnvironmentalLight;
use GreatMarketrealmTabletop\Tabletop\Light\Models\MagicalLight;
use GreatMarketrealmTabletop\Tabletop\SceneObjects\Repositories\WordPressSceneObjectRepository;
use GreatMarketrealmTabletop\Tabletop\SceneObjects\SceneObjectVisionProjector;
use DateTimeImmutable;

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
        $sceneObjects = [];
        $sceneObjectVision = new SceneObjectVisionProjector();

        // Scene Objects already persist by exact Table + Scene identity. The
        // Living Veil reads the same authoritative records rather than copying
        // furniture into the Keeper's hand-drawn Vision Barrier repository.
        foreach ($tokens as $candidate) {
            if (! $candidate instanceof TableToken) {
                continue;
            }

            $sceneObjects = (new WordPressSceneObjectRepository())->forScene(
                $candidate->tableId(),
                $scene->id()
            );
            break;
        }

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
            $tokenVisible = $mapper->visibleAround(
                $scene,
                $token,
                $barriers,
                $visionRadius
            );
            $tokenVisible = $sceneObjectVision->filterVisibleCells(
                $scene,
                $token,
                $tokenVisible,
                $sceneObjects
            );
            $visible = array_merge($visible, $tokenVisible);

            // Shared illumination may be seen beyond the adventurer's natural
            // sight radius, but only where the viewer has an unobstructed line
            // of sight. Scene Object vision blockers participate here too;
            // light attenuation itself remains a separate IV.35.4C concern.
            $viewerSight = $mapper->visibleAround($scene, $token, $barriers, 60);
            $viewerSight = $sceneObjectVision->filterVisibleCells(
                $scene,
                $token,
                $viewerSight,
                $sceneObjects
            );
            $viewerLineOfSight = array_merge(
                $viewerLineOfSight,
                $viewerSight
            );
        }

        $visible = array_values(array_unique($visible));
        $viewerLineOfSight = array_values(array_unique($viewerLineOfSight));
        $safeLightSources = [];

        foreach ($worldLightSources as $lightSource) {
            $sourceKind = 'carried';
            $brightFeet = 20;
            $dimFeet = 20;
            if ($lightSource instanceof EnvironmentalLight) {
                if (! $lightSource->lit()) {
                    continue;
                }

                $sourceKind = 'environmental';
                $brightFeet = $lightSource->brightFeet();
                $dimFeet = $lightSource->dimFeet();
                $lightSource = TableToken::create(
                    $lightSource->id(), $lightSource->tableId(), $lightSource->sceneId(),
                    $lightSource->label(), TableTokenType::OBJECT, 'environmental-' . $lightSource->kind(), null,
                    $lightSource->x(), $lightSource->y(), 1, 1, 'visible', new DateTimeImmutable()
                );
            } elseif ($lightSource instanceof DroppedLight) {
                $sourceKind = 'dropped';
                $lightSource = TableToken::create(
                    $lightSource->id(), $lightSource->tableId(), $lightSource->sceneId(),
                    'Dropped Torch', TableTokenType::OBJECT, 'dropped-torch', null,
                    $lightSource->x(), $lightSource->y(), 1, 1, 'visible', new DateTimeImmutable()
                );
            } elseif (
                is_array($lightSource)
                && ($lightSource['magical_light'] ?? null) instanceof MagicalLight
                && ($lightSource['token'] ?? null) instanceof TableToken
            ) {
                $magicalLight = $lightSource['magical_light'];
                $sourceKind = 'magical';
                $brightFeet = $magicalLight->brightFeet();
                $dimFeet = $magicalLight->dimFeet();
                $lightSource = $lightSource['token'];
            }
            if (! $lightSource instanceof TableToken || ! in_array($lightSource->type(), [TableTokenType::CHARACTER, TableTokenType::OBJECT], true)) {
                continue;
            }

            $mapper = new FogCellMapper();
            $lightRadius = max(1, (int) ceil(($brightFeet + $dimFeet) / 5));
            $illuminated = $mapper->visibleAround($scene, $lightSource, $barriers, $lightRadius);
            $sharedVisible = array_values(array_intersect($illuminated, $viewerLineOfSight));
            $visible = array_merge($visible, $sharedVisible);

            $sourceCell = $mapper->cellFor($scene, $lightSource->x(), $lightSource->y());
            $sourceKey = FogCellMapper::key($sourceCell['column'], $sourceCell['row']);
            if ($dungeonMaster || in_array($sourceKey, $visible, true)) {
                $safeLightSources[] = [
                    'x' => $lightSource->x(),
                    'y' => $lightSource->y(),
                    'token_id' => $lightSource->id(),
                    'range_feet' => $brightFeet + $dimFeet,
                    'bright_light_feet' => $brightFeet,
                    'dim_light_feet' => $dimFeet,
                    'shared' => true,
                    'source_kind' => $sourceKind,
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
            'has_blockers' => $barriers !== [] || $sceneObjects !== [],
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
