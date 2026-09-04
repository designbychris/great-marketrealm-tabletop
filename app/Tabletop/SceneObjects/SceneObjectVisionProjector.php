<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\SceneObjects;

use GreatMarketrealmTabletop\Tables\Scenes\Models\TableScene;
use GreatMarketrealmTabletop\Tables\Tokens\Models\TableToken;
use GreatMarketrealmTabletop\Tabletop\SceneObjects\Models\SceneObject;

defined('ABSPATH') || exit;

/**
 * Projects sight-blocking Scene Objects into the server-authoritative
 * Living Veil without turning furnishings into persisted wall records.
 */
final class SceneObjectVisionProjector
{
    public function __construct(
        private ?FurnitureCatalogue $catalogue = null
    ) {}

    /**
     * @param array<int,string> $visibleCells
     * @param array<int,SceneObject> $objects
     * @return array<int,string>
     */
    public function filterVisibleCells(
        TableScene $scene,
        TableToken $viewer,
        array $visibleCells,
        array $objects
    ): array {
        $blockers = $this->blockerPolygons($scene, $objects);

        if ($blockers === []) {
            return $visibleCells;
        }

        $origin = [
            'x' => $this->clamp01($viewer->x()),
            'y' => $this->clamp01($viewer->y()),
        ];

        return array_values(array_filter(
            $visibleCells,
            function (string $key) use ($scene, $origin, $blockers): bool {
                $target = $this->cellCentre($scene, $key);
                if ($target === null) {
                    return true;
                }

                foreach ($blockers as $polygon) {
                    // The face of the obstacle should remain visible; only
                    // cells beyond it are removed from the viewer's sight.
                    if ($this->pointInPolygon($target, $polygon)) {
                        continue;
                    }

                    if ($this->lineIntersectsPolygon($origin, $target, $polygon)) {
                        return false;
                    }
                }

                return true;
            }
        ));
    }

    /**
     * @param array<int,SceneObject> $objects
     * @return array<int,array<int,array{x:float,y:float}>>
     */
    private function blockerPolygons(TableScene $scene, array $objects): array
    {
        $catalogue = $this->catalogue ?? new FurnitureCatalogue();
        $polygons = [];
        $sceneWidth = max(1.0, (float) $scene->width());
        $sceneHeight = max(1.0, (float) $scene->height());
        $grid = max(1.0, (float) $scene->gridSize());

        foreach ($objects as $object) {
            if (! $object instanceof SceneObject) {
                continue;
            }

            $properties = $object->properties();
            $definition = $catalogue->find($object->kind()) ?? [];
            $blocksVision = array_key_exists('blocks_vision', $properties)
                ? ! empty($properties['blocks_vision'])
                : ! empty($definition['blocks_vision']);

            if (! $blocksVision) {
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

            $polygons[] = $this->rectangleCorners(
                $this->clamp01($object->x()),
                $this->clamp01($object->y()),
                ($widthUnits * $grid * $scale) / $sceneWidth,
                ($heightUnits * $grid * $scale) / $sceneHeight,
                $object->rotation()
            );
        }

        return $polygons;
    }

    /** @return array{x:float,y:float}|null */
    private function cellCentre(TableScene $scene, string $key): ?array
    {
        $parts = explode(':', $key, 2);
        if (count($parts) !== 2 || ! is_numeric($parts[0]) || ! is_numeric($parts[1])) {
            return null;
        }

        $column = (int) $parts[0];
        $row = (int) $parts[1];
        $grid = max(1.0, (float) $scene->gridSize());
        $width = max(1.0, (float) $scene->width());
        $height = max(1.0, (float) $scene->height());

        return [
            'x' => $this->clamp01(
                ((float) $scene->gridOffsetX() + (($column + 0.5) * $grid)) / $width
            ),
            'y' => $this->clamp01(
                ((float) $scene->gridOffsetY() + (($row + 0.5) * $grid)) / $height
            ),
        ];
    }

    /**
     * @return array<int,array{x:float,y:float}>
     */
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
        $corners = [
            [-$halfWidth, -$halfHeight],
            [$halfWidth, -$halfHeight],
            [$halfWidth, $halfHeight],
            [-$halfWidth, $halfHeight],
        ];

        return array_map(
            static function (array $corner) use ($cx, $cy, $cos, $sin): array {
                return [
                    'x' => $cx + ($corner[0] * $cos) - ($corner[1] * $sin),
                    'y' => $cy + ($corner[0] * $sin) + ($corner[1] * $cos),
                ];
            },
            $corners
        );
    }

    /**
     * @param array{x:float,y:float} $point
     * @param array<int,array{x:float,y:float}> $polygon
     */
    private function pointInPolygon(array $point, array $polygon): bool
    {
        $inside = false;
        $count = count($polygon);

        for ($i = 0, $j = $count - 1; $i < $count; $j = $i++) {
            $a = $polygon[$i];
            $b = $polygon[$j];
            $crosses = (($a['y'] > $point['y']) !== ($b['y'] > $point['y']))
                && ($point['x'] < (($b['x'] - $a['x']) * ($point['y'] - $a['y']) / (($b['y'] - $a['y']) ?: 1.0e-12)) + $a['x']);
            if ($crosses) {
                $inside = ! $inside;
            }
        }

        return $inside;
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
