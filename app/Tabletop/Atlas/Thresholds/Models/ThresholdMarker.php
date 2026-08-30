<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Atlas\Thresholds\Models;

use DateTimeImmutable;
use InvalidArgumentException;

defined('ABSPATH') || exit;

final class ThresholdMarker
{
    private function __construct(
        private string $id,
        private string $tableId,
        private string $sceneId,
        private string $type,
        private float $x,
        private float $y,
        private DateTimeImmutable $createdAt
    ) {}

    public static function create(
        string $id,
        string $tableId,
        string $sceneId,
        string $type,
        float $x,
        float $y,
        DateTimeImmutable $createdAt
    ): self {
        $id = trim($id);
        $tableId = trim($tableId);
        $sceneId = trim($sceneId);
        if ($id === '' || $tableId === '' || $sceneId === '') {
            throw new InvalidArgumentException('A Threshold Marker requires an ID, Table and Scene.');
        }
        if ($x < 0 || $x > 1 || $y < 0 || $y > 1) {
            throw new InvalidArgumentException('Threshold coordinates must remain between 0 and 1.');
        }

        return new self($id, $tableId, $sceneId, ThresholdType::assert($type), $x, $y, $createdAt);
    }

    /** @param array<string,mixed> $record */
    public static function reconstitute(array $record): self
    {
        return self::create(
            (string) ($record['id'] ?? ''),
            (string) ($record['table_id'] ?? ''),
            (string) ($record['scene_id'] ?? ''),
            (string) ($record['type'] ?? ''),
            (float) ($record['x'] ?? 0),
            (float) ($record['y'] ?? 0),
            new DateTimeImmutable((string) ($record['created_at'] ?? 'now'))
        );
    }

    public function id(): string { return $this->id; }
    public function tableId(): string { return $this->tableId; }
    public function sceneId(): string { return $this->sceneId; }
    public function type(): string { return $this->type; }
    public function x(): float { return $this->x; }
    public function y(): float { return $this->y; }

    /** @return array<string,mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'table_id' => $this->tableId,
            'scene_id' => $this->sceneId,
            'type' => $this->type,
            'x' => $this->x,
            'y' => $this->y,
            'created_at' => $this->createdAt->format(DATE_ATOM),
        ];
    }
}
