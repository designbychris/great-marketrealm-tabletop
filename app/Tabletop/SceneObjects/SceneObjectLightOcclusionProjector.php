<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\SceneObjects;

use GreatMarketrealmTabletop\Tables\Scenes\Models\TableScene;
use GreatMarketrealmTabletop\Tabletop\SceneObjects\Models\SceneObject;

defined('ABSPATH') || exit;

/**
 * IV.35.4C.1 — reusable Scene Object light-obstruction geometry.
 *
 * This projector deliberately does not mutate the Living Veil yet. It answers
 * one mechanical question for the existing lighting engine: how much of a ray
 * from one normalised Scene point to another is occluded by placed objects?
 * IV.35.4C.2 can consume this seam without creating a second light system.
 */
final class SceneObjectLightOcclusionProjector
{
    public function __construct(
        private ?FurnitureCatalogue $catalogue = null
    ) {}

    /**
     * Returns cumulative opacity in the inclusive range 0.0–1.0.
     * Multiple partial blockers compound rather than merely taking the largest.
     *
     * @param array{x:float,y:float} $origin
     * @param array{x:float,y:float} $target
     * @param array<int,SceneObject> $objects
     */
    public function occlusionBetween(
        TableScene $scene,
        array $origin,
        array $target,
        array $objects
    ): float {
        $transmission = 1.0;

        foreach ($this->occluders($scene, $objects) as $occluder) {
            if (! $this->lineIntersectsPolygon($origin, $target, $occluder['polygon'])) {
                continue;
            }

            $transmission *= 1.0 - $occluder['occlusion'];
            if ($transmission <= 1.0e-9) {
                return 1.0;
            }
        }

        return $this->clamp01(1.0 - $transmission);
    }

    /**
     * @param array<int,SceneObject> $objects
     * @return array<int,array{occlusion:float,polygon:array<int,array{x:float,y:float}>}>
     */
    public function occluders(TableScene $scene, array $objects): array
    {
        $catalogue = $this->catalogue ?? new FurnitureCatalogue();
        $sceneWidth = max(1.0, (float) $scene->width());
        $sceneHeight = max(1.0, (float) $scene->height());
        $grid = max(1.0, (float) $scene->gridSize());
        $result = [];

        foreach ($objects as $object) {
            if (! $object instanceof SceneObject) {
                continue;
            }

            $properties = $object->properties();
            $definition = $catalogue->find($object->kind()) ?? [];
            $occlusion = $this->clamp01((float) (
                $properties['light_occlusion']
                ?? ($definition['light_occlusion'] ?? 0.0)
            ));

            if ($occlusion <= 0.0) {
                continue;
            }

            $widthUnits = max(0.25, (float) (
                $properties['width_units']
                ?? ($definition['width_units'] ?? 1.0)
            ));
            $heightUnits = max(0.25, (float) (
                $properties['height_units']
                ?? ($definition['height_units'] ?? 1.0)
            ));
            $scale = max(0.01, $object->scale());

            $result[] = [
                'occlusion' => $occlusion,
                'polygon' => $this->rectangleCorners(
                    $this->clamp01($object->x()),
                    $this->clamp01($object->y()),
                    ($widthUnits * $grid * $scale) / $sceneWidth,
                    ($heightUnits * $grid * $scale) / $sceneHeight,
                    $object->rotation()
                ),
            ];
        }

        return $result;
    }

    /** @return array<int,array{x:float,y:float}> */
    private function rectangleCorners(
        float $cx,
        float $cy,
        float $width,
        float $height,
        int $rotationDegrees
    ): array {
        $halfWidth = $width / 2;
        $halfHeight = $height / 2;
        $radians = deg2rad((float) $rotationDegrees);
        $cos = cos($radians);
        $sin = sin($radians);

        return array_map(
            static function (array $corner) use ($cx, $cy, $cos, $sin): array {
                return [
                    'x' => $cx + ($corner[0] * $cos) - ($corner[1] * $sin),
                    'y' => $cy + ($corner[0] * $sin) + ($corner[1] * $cos),
                ];
            },
            [
                [-$halfWidth, -$halfHeight],
                [$halfWidth, -$halfHeight],
                [$halfWidth, $halfHeight],
                [-$halfWidth, $halfHeight],
            ]
        );
    }

    /**
     * @param array{x:float,y:float} $start
     * @param array{x:float,y:float} $end
     * @param array<int,array{x:float,y:float}> $polygon
     */
    private function lineIntersectsPolygon(array $start, array $end, array $polygon): bool
    {
        $count = count($polygon);
        for ($index = 0; $index < $count; ++$index) {
            if ($this->segmentsIntersect(
                $start,
                $end,
                $polygon[$index],
                $polygon[($index + 1) % $count]
            )) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array{x:float,y:float} $a
     * @param array{x:float,y:float} $b
     * @param array{x:float,y:float} $c
     * @param array{x:float,y:float} $d
     */
    private function segmentsIntersect(array $a, array $b, array $c, array $d): bool
    {
        $denominator = (($d['y'] - $c['y']) * ($b['x'] - $a['x']))
            - (($d['x'] - $c['x']) * ($b['y'] - $a['y']));

        if (abs($denominator) < 1.0e-12) {
            return false;
        }

        $ua = ((($d['x'] - $c['x']) * ($a['y'] - $c['y']))
            - (($d['y'] - $c['y']) * ($a['x'] - $c['x']))) / $denominator;
        $ub = ((($b['x'] - $a['x']) * ($a['y'] - $c['y']))
            - (($b['y'] - $a['y']) * ($a['x'] - $c['x']))) / $denominator;

        return $ua > 1.0e-9 && $ua < 1.0 - 1.0e-9
            && $ub >= 0.0 && $ub <= 1.0;
    }

    private function clamp01(float $value): float
    {
        return max(0.0, min(1.0, $value));
    }
}
