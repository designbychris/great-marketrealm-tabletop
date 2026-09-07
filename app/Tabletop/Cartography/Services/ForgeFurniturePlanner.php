<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Cartography\Services;

use GreatMarketrealmTabletop\Tabletop\SceneObjects\FurnitureCatalogue;

defined('ABSPATH') || exit;

/**
 * Deterministic interior-design pass for Dungeon Forge plans.
 *
 * Architecture remains authoritative. This planner only proposes furniture
 * inside major room rectangles, keeps doorway approaches clear and rejects
 * overlapping footprints before any Scene Object is persisted.
 */
final class ForgeFurniturePlanner
{
    public function __construct(
        private FurnitureCatalogue $catalogue
    ) {}

    /**
     * @param array<string,mixed> $plan
     * @return array<int,array{
     *   kind:string,x:float,y:float,rotation:int,room_index:int,room_role:string
     * }>
     */
    public function plan(array $plan): array
    {
        $cols = max(1, (int) ($plan['cols'] ?? 1));
        $rows = max(1, (int) ($plan['rows'] ?? 1));
        $seed = (string) ($plan['seed'] ?? 'Peppercorn-01');
        $sceneType = (string) ($plan['scene_type'] ?? 'dungeon');
        $rooms = is_array($plan['rooms'] ?? null) ? $plan['rooms'] : [];
        $doors = is_array($plan['doors'] ?? null) ? $plan['doors'] : [];

        $placed = [];
        $drafts = [];
        $maximum = $sceneType === 'forest' ? 12 : 36;

        foreach ($rooms as $roomIndex => $room) {
            if (! is_array($room) || count($drafts) >= $maximum) {
                continue;
            }

            $x = (float) ($room['x'] ?? 0);
            $y = (float) ($room['y'] ?? 0);
            $w = (float) ($room['w'] ?? 0);
            $h = (float) ($room['h'] ?? 0);

            // Tiny connector spaces and corridor-like rectangles remain empty.
            if ($w < 3.0 || $h < 3.0 || ($w * $h) < 12.0) {
                continue;
            }

            $role = $this->roomRole($sceneType, (int) $roomIndex, $seed);
            $candidates = $this->candidatesForRole($role, $x, $y, $w, $h, $seed, (int) $roomIndex);

            foreach ($candidates as $candidateIndex => $candidate) {
                if (count($drafts) >= $maximum) {
                    break 2;
                }

                $kind = (string) ($candidate['kind'] ?? '');
                $definition = $this->catalogue->find($kind);
                if (! is_array($definition)) {
                    continue;
                }

                $rotation = ((int) ($candidate['rotation'] ?? 0) % 360 + 360) % 360;
                $width = max(0.25, (float) ($definition['width_units'] ?? 1.0));
                $height = max(0.25, (float) ($definition['height_units'] ?? 1.0));
                if (in_array($rotation, [90, 270], true)) {
                    [$width, $height] = [$height, $width];
                }

                $gridX = (float) ($candidate['x'] ?? ($x + ($w / 2)));
                $gridY = (float) ($candidate['y'] ?? ($y + ($h / 2)));
                $footprint = [
                    'left' => $gridX - ($width / 2),
                    'right' => $gridX + ($width / 2),
                    'top' => $gridY - ($height / 2),
                    'bottom' => $gridY + ($height / 2),
                ];

                if (! $this->insideRoom($footprint, $x, $y, $w, $h)) {
                    continue;
                }
                if ($this->nearDoor($gridX, $gridY, $doors, $cols, $rows)) {
                    continue;
                }
                if ($this->overlaps($footprint, $placed)) {
                    continue;
                }

                $placed[] = $footprint;
                $drafts[] = [
                    'kind' => $kind,
                    'x' => max(0.0, min(1.0, $gridX / $cols)),
                    'y' => max(0.0, min(1.0, $gridY / $rows)),
                    'rotation' => $rotation,
                    'room_index' => (int) $roomIndex,
                    'room_role' => $role,
                ];
            }
        }

        return $drafts;
    }

    private function roomRole(string $sceneType, int $roomIndex, string $seed): string
    {
        if ($sceneType === 'village') {
            // Village Forge creates the inn first, then cottage/workshop/house
            // rectangles. Give those spaces stable, legible furnishing intent.
            return match ($roomIndex) {
                0 => 'mess',
                1 => 'quarters',
                2 => 'store',
                default => $this->pick($seed, 'village-role-' . $roomIndex, ['quarters', 'mess', 'store']),
            };
        }

        if ($sceneType === 'forest') {
            return $this->pick($seed, 'forest-role-' . $roomIndex, ['camp', 'cache', 'empty']);
        }

        return $this->pick(
            $seed,
            'dungeon-role-' . $roomIndex,
            ['mess', 'store', 'study', 'treasure', 'quarters']
        );
    }

    /**
     * @return array<int,array{kind:string,x:float,y:float,rotation:int}>
     */
    private function candidatesForRole(
        string $role,
        float $x,
        float $y,
        float $w,
        float $h,
        string $seed,
        int $roomIndex
    ): array {
        $cx = $x + ($w / 2);
        $cy = $y + ($h / 2);
        $left = $x + 1.0;
        $right = $x + $w - 1.0;
        $top = $y + 1.0;
        $bottom = $y + $h - 1.0;
        $longRotation = $w >= $h ? 0 : 90;

        $sets = [
            'mess' => [
                ['kind' => 'table', 'x' => $cx, 'y' => $cy, 'rotation' => $longRotation],
                ['kind' => 'bench', 'x' => $cx, 'y' => $cy + 1.15, 'rotation' => $longRotation],
                ['kind' => 'stool', 'x' => $left, 'y' => $bottom, 'rotation' => 0],
                ['kind' => 'barrel', 'x' => $right, 'y' => $bottom, 'rotation' => 0],
            ],
            'store' => [
                ['kind' => 'cupboard', 'x' => $cx, 'y' => $top, 'rotation' => 0],
                ['kind' => 'barrel', 'x' => $right, 'y' => $top, 'rotation' => 0],
                ['kind' => 'sacks', 'x' => $right, 'y' => $bottom, 'rotation' => 0],
                ['kind' => 'crate', 'x' => $left, 'y' => $bottom, 'rotation' => 0],
            ],
            'study' => [
                ['kind' => 'bookshelf', 'x' => $cx, 'y' => $top, 'rotation' => 0],
                ['kind' => 'desk', 'x' => $cx, 'y' => $cy + 0.65, 'rotation' => $longRotation],
                ['kind' => 'chair', 'x' => $cx, 'y' => $cy - 0.85, 'rotation' => 180],
                ['kind' => 'rug', 'x' => $right - 0.5, 'y' => $bottom - 0.5, 'rotation' => 0],
            ],
            'treasure' => [
                ['kind' => 'chest', 'x' => $cx, 'y' => $top, 'rotation' => 0],
                ['kind' => 'crate', 'x' => $left, 'y' => $bottom, 'rotation' => 0],
                ['kind' => 'barrel', 'x' => $right, 'y' => $bottom, 'rotation' => 0],
            ],
            'quarters' => [
                ['kind' => 'bed', 'x' => $left + 0.5, 'y' => $cy, 'rotation' => $h > $w ? 90 : 0],
                ['kind' => 'desk', 'x' => $right - 0.25, 'y' => $top, 'rotation' => 0],
                ['kind' => 'stool', 'x' => $right, 'y' => $cy, 'rotation' => 0],
                ['kind' => 'chest', 'x' => $right, 'y' => $bottom, 'rotation' => 0],
            ],
            'camp' => [
                ['kind' => 'campfire', 'x' => $cx, 'y' => $cy, 'rotation' => 0],
                ['kind' => 'sacks', 'x' => $left, 'y' => $bottom, 'rotation' => 0],
                ['kind' => 'crate', 'x' => $right, 'y' => $bottom, 'rotation' => 0],
            ],
            'cache' => [
                ['kind' => 'chest', 'x' => $cx, 'y' => $cy, 'rotation' => 0],
                ['kind' => 'crate', 'x' => $right, 'y' => $bottom, 'rotation' => 0],
            ],
            'empty' => [],
        ];

        $candidates = $sets[$role] ?? [];

        // Deterministically trim one optional secondary object from some rooms,
        // keeping the Forge lived-in without filling every tactical square.
        if (
            count($candidates) > 2
            && $this->fraction($seed, 'trim-' . $roomIndex . '-' . $role) < 0.34
        ) {
            array_pop($candidates);
        }

        return $candidates;
    }

    /** @param array{left:float,right:float,top:float,bottom:float} $footprint */
    private function insideRoom(array $footprint, float $x, float $y, float $w, float $h): bool
    {
        $margin = 0.25;
        return $footprint['left'] >= ($x + $margin)
            && $footprint['right'] <= ($x + $w - $margin)
            && $footprint['top'] >= ($y + $margin)
            && $footprint['bottom'] <= ($y + $h - $margin);
    }

    /** @param array<int,mixed> $doors */
    private function nearDoor(float $x, float $y, array $doors, int $cols, int $rows): bool
    {
        foreach ($doors as $door) {
            if (! is_array($door)) {
                continue;
            }
            $doorX = ((((float) ($door['x1'] ?? 0)) + ((float) ($door['x2'] ?? 0))) / 2) * $cols;
            $doorY = ((((float) ($door['y1'] ?? 0)) + ((float) ($door['y2'] ?? 0))) / 2) * $rows;

            // Keep a broad two-square approach clear around every generated door.
            if (max(abs($x - $doorX), abs($y - $doorY)) < 2.0) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param array{left:float,right:float,top:float,bottom:float} $candidate
     * @param array<int,array{left:float,right:float,top:float,bottom:float}> $placed
     */
    private function overlaps(array $candidate, array $placed): bool
    {
        $clearance = 0.18;
        foreach ($placed as $other) {
            if (
                $candidate['right'] + $clearance <= $other['left']
                || $candidate['left'] - $clearance >= $other['right']
                || $candidate['bottom'] + $clearance <= $other['top']
                || $candidate['top'] - $clearance >= $other['bottom']
            ) {
                continue;
            }
            return true;
        }
        return false;
    }

    /** @param array<int,string> $choices */
    private function pick(string $seed, string $salt, array $choices): string
    {
        if ($choices === []) {
            return '';
        }
        $index = min(
            count($choices) - 1,
            (int) floor($this->fraction($seed, $salt) * count($choices))
        );
        return $choices[$index];
    }

    private function fraction(string $seed, string $salt): float
    {
        $hex = substr(hash('sha256', $seed . '|' . $salt), 0, 8);
        return hexdec($hex) / 4294967295;
    }
}
