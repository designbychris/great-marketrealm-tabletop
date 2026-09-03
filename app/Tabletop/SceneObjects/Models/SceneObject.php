<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\SceneObjects\Models;

use InvalidArgumentException;

defined('ABSPATH') || exit;

/**
 * A persistent object placed upon a Tabletop Scene.
 *
 * Scene Objects deliberately live above the Scene surface: furniture and
 * future interactive props must never be baked into uploaded or generated
 * battlemap artwork.
 */
final class SceneObject
{
    /** @param array<string,mixed> $state @param array<string,mixed> $properties */
    public function __construct(
        private string $id,
        private string $tableId,
        private string $sceneId,
        private string $kind,
        private string $category,
        private float $x,
        private float $y,
        private int $rotation = 0,
        private float $scale = 1.0,
        private array $state = [],
        private array $properties = []
    ) {
        foreach ([$id, $tableId, $sceneId, $kind, $category] as $value) {
            if (trim($value) === '') {
                throw new InvalidArgumentException('Scene Object identity and classification cannot be empty.');
            }
        }
        if ($x < 0.0 || $x > 1.0 || $y < 0.0 || $y > 1.0) {
            throw new InvalidArgumentException('Scene Object coordinates must be normalised between 0 and 1.');
        }
        if ($scale <= 0.0) {
            throw new InvalidArgumentException('Scene Object scale must be greater than zero.');
        }
        $this->rotation = (($rotation % 360) + 360) % 360;
    }

    public function id(): string { return $this->id; }
    public function tableId(): string { return $this->tableId; }
    public function sceneId(): string { return $this->sceneId; }
    public function kind(): string { return $this->kind; }
    public function category(): string { return $this->category; }
    public function x(): float { return $this->x; }
    public function y(): float { return $this->y; }
    public function rotation(): int { return $this->rotation; }
    public function scale(): float { return $this->scale; }
    /** @return array<string,mixed> */ public function state(): array { return $this->state; }
    /** @return array<string,mixed> */ public function properties(): array { return $this->properties; }

    /** @return array<string,mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'table_id' => $this->tableId,
            'scene_id' => $this->sceneId,
            'kind' => $this->kind,
            'category' => $this->category,
            'x' => $this->x,
            'y' => $this->y,
            'rotation' => $this->rotation,
            'scale' => $this->scale,
            'state' => $this->state,
            'properties' => $this->properties,
        ];
    }

    /** @param array<string,mixed> $record */
    public static function fromArray(array $record): self
    {
        return new self(
            (string) ($record['id'] ?? ''),
            (string) ($record['table_id'] ?? ''),
            (string) ($record['scene_id'] ?? ''),
            (string) ($record['kind'] ?? ''),
            (string) ($record['category'] ?? ''),
            (float) ($record['x'] ?? 0.0),
            (float) ($record['y'] ?? 0.0),
            (int) ($record['rotation'] ?? 0),
            (float) ($record['scale'] ?? 1.0),
            is_array($record['state'] ?? null) ? $record['state'] : [],
            is_array($record['properties'] ?? null) ? $record['properties'] : []
        );
    }
}
