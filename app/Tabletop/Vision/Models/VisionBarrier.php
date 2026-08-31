<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Vision\Models;

use InvalidArgumentException;

defined('ABSPATH') || exit;

final class VisionBarrier
{
    public const WALL = 'wall';
    public const DOOR = 'door';

    /** @var array<int,array{x:float,y:float}> */
    private array $points;

    /**
     * @param array<int,array{x:mixed,y:mixed}|array{0:mixed,1:mixed}> $points
     */
    public function __construct(
        private string $id,
        private string $sceneId,
        private string $type,
        private float $x1,
        private float $y1,
        private float $x2,
        private float $y2,
        private bool $open = false,
        array $points = []
    ) {
        if (trim($id) === '' || trim($sceneId) === '' || ! in_array($type, [self::WALL, self::DOOR], true)) {
            throw new InvalidArgumentException('A vision barrier requires a valid identity, type and two distinct grid intersections.');
        }

        $this->points = $this->normalisePoints($points);
        if ($this->points === []) {
            if ($x1 === $x2 && $y1 === $y2) {
                throw new InvalidArgumentException('A vision barrier requires a valid identity, type and two distinct grid intersections.');
            }
            $this->points = [
                ['x' => $x1, 'y' => $y1],
                ['x' => $x2, 'y' => $y2],
            ];
        }

        if ($type === self::DOOR && count($this->points) !== 2) {
            throw new InvalidArgumentException('A vision door must remain a single two-point barrier.');
        }

        $this->x1 = $this->points[0]['x'];
        $this->y1 = $this->points[0]['y'];
        $last = $this->points[count($this->points) - 1];
        $this->x2 = $last['x'];
        $this->y2 = $last['y'];

        if ($type === self::WALL) {
            $this->open = false;
        }
    }

    /** @param array<string,mixed> $record */
    public static function reconstitute(array $record): self
    {
        $points = is_array($record['points'] ?? null) ? $record['points'] : [];

        return new self(
            (string) ($record['id'] ?? ''),
            (string) ($record['scene_id'] ?? ''),
            (string) ($record['type'] ?? ''),
            (float) ($record['x1'] ?? 0),
            (float) ($record['y1'] ?? 0),
            (float) ($record['x2'] ?? 0),
            (float) ($record['y2'] ?? 0),
            (bool) ($record['open'] ?? false),
            $points
        );
    }

    /**
     * @param array<int,array{x:mixed,y:mixed}|array{0:mixed,1:mixed}> $points
     */
    public static function path(string $id, string $sceneId, array $points): self
    {
        if (count($points) < 2) {
            throw new InvalidArgumentException('A vision barrier path requires at least two points.');
        }
        $first = $points[0];
        $last = $points[count($points) - 1];
        $x1 = (float) ($first['x'] ?? $first[0] ?? 0);
        $y1 = (float) ($first['y'] ?? $first[1] ?? 0);
        $x2 = (float) ($last['x'] ?? $last[0] ?? 0);
        $y2 = (float) ($last['y'] ?? $last[1] ?? 0);

        return new self($id, $sceneId, self::WALL, $x1, $y1, $x2, $y2, false, $points);
    }

    public function id(): string { return $this->id; }
    public function sceneId(): string { return $this->sceneId; }
    public function type(): string { return $this->type; }
    public function x1(): float { return $this->x1; }
    public function y1(): float { return $this->y1; }
    public function x2(): float { return $this->x2; }
    public function y2(): float { return $this->y2; }
    public function isOpen(): bool { return $this->open; }
    public function blocksSight(): bool { return $this->type === self::WALL || ! $this->open; }
    public function isPath(): bool { return count($this->points) > 2; }

    /** @return array<int,array{x:float,y:float}> */
    public function points(): array
    {
        return $this->points;
    }

    /** @return array<int,array{x1:float,y1:float,x2:float,y2:float}> */
    public function segments(): array
    {
        $segments = [];
        for ($index = 0; $index < count($this->points) - 1; $index++) {
            $start = $this->points[$index];
            $end = $this->points[$index + 1];
            $segments[] = [
                'x1' => $start['x'],
                'y1' => $start['y'],
                'x2' => $end['x'],
                'y2' => $end['y'],
            ];
        }
        return $segments;
    }

    public function toggleDoor(): void
    {
        if ($this->type !== self::DOOR) {
            throw new InvalidArgumentException('Only doors may be opened or closed.');
        }
        $this->open = ! $this->open;
    }

    /** @return array<string,mixed> */
    public function toArray(): array
    {
        $record = [
            'id' => $this->id,
            'scene_id' => $this->sceneId,
            'type' => $this->type,
            'x1' => $this->x1,
            'y1' => $this->y1,
            'x2' => $this->x2,
            'y2' => $this->y2,
            'open' => $this->open,
        ];
        if ($this->isPath()) {
            $record['points'] = $this->points;
        }
        return $record;
    }

    /**
     * @param array<int,array{x:mixed,y:mixed}|array{0:mixed,1:mixed}> $points
     * @return array<int,array{x:float,y:float}>
     */
    private function normalisePoints(array $points): array
    {
        $normalised = [];
        foreach ($points as $point) {
            if (! is_array($point)) {
                continue;
            }
            $x = (float) ($point['x'] ?? $point[0] ?? 0);
            $y = (float) ($point['y'] ?? $point[1] ?? 0);
            $candidate = ['x' => $x, 'y' => $y];
            $previous = $normalised[count($normalised) - 1] ?? null;
            if ($previous !== null && $previous['x'] === $x && $previous['y'] === $y) {
                continue;
            }
            $normalised[] = $candidate;
        }

        if ($normalised !== [] && count($normalised) < 2) {
            throw new InvalidArgumentException('A vision barrier path requires at least two distinct points.');
        }
        return $normalised;
    }
}
