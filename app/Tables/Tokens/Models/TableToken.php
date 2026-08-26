<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tables\Tokens\Models;

use DateTimeImmutable;
use InvalidArgumentException;

defined('ABSPATH') || exit;

final class TableToken
{
    private function __construct(
        private string $id,
        private string $tableId,
        private string $sceneId,
        private string $label,
        private string $type,
        private ?string $sourceReference,
        private ?int $controllerUserId,
        private float $x,
        private float $y,
        private float $widthUnits,
        private float $heightUnits,
        private string $visibility,
        private DateTimeImmutable $createdAt,
        private int $revision = 1
    ) {}

    public static function create(
        string $id,
        string $tableId,
        string $sceneId,
        string $label,
        string $type,
        ?string $sourceReference,
        ?int $controllerUserId,
        float $x,
        float $y,
        float $widthUnits,
        float $heightUnits,
        string $visibility,
        DateTimeImmutable $createdAt
    ): self {
        self::assertIdentity(
            $id,
            $tableId,
            $sceneId,
            $label
        );

        $type = TableTokenType::assert($type);
        $visibility = TableTokenVisibility::assert(
            $visibility
        );

        self::assertSourceReference(
            $type,
            $sourceReference
        );
        self::assertController($controllerUserId);
        self::assertCoordinates($x, $y);
        self::assertFootprint(
            $widthUnits,
            $heightUnits
        );

        return new self(
            trim($id),
            trim($tableId),
            trim($sceneId),
            trim($label),
            $type,
            self::reference($sourceReference),
            $controllerUserId,
            $x,
            $y,
            $widthUnits,
            $heightUnits,
            $visibility,
            $createdAt,
            1
        );
    }

    /** @param array<string,mixed> $record */
    public static function reconstitute(array $record): self
    {
        $token = self::create(
            (string) ($record['id'] ?? ''),
            (string) ($record['table_id'] ?? ''),
            (string) ($record['scene_id'] ?? ''),
            (string) ($record['label'] ?? ''),
            (string) ($record['type'] ?? ''),
            is_string(
                $record['source_reference'] ?? null
            )
                ? (string) $record['source_reference']
                : null,
            isset($record['controller_user_id'])
                ? (int) $record['controller_user_id']
                : null,
            (float) ($record['x'] ?? 0),
            (float) ($record['y'] ?? 0),
            (float) ($record['width_units'] ?? 1),
            (float) ($record['height_units'] ?? 1),
            (string) ($record['visibility'] ?? ''),
            new DateTimeImmutable(
                (string) (
                    $record['created_at']
                    ?? 'now'
                )
            )
        );

        $token->revision = max(
            1,
            (int) ($record['revision'] ?? 1)
        );

        return $token;
    }

    public function move(float $x, float $y): void
    {
        self::assertCoordinates($x, $y);

        $this->x = $x;
        $this->y = $y;
        ++$this->revision;
    }

    public function resize(
        float $widthUnits,
        float $heightUnits
    ): void {
        self::assertFootprint(
            $widthUnits,
            $heightUnits
        );

        $this->widthUnits = $widthUnits;
        $this->heightUnits = $heightUnits;
        ++$this->revision;
    }

    public function show(): void
    {
        if (
            $this->visibility
            !== TableTokenVisibility::VISIBLE
        ) {
            $this->visibility =
                TableTokenVisibility::VISIBLE;
            ++$this->revision;
        }
    }

    public function hide(): void
    {
        if (
            $this->visibility
            !== TableTokenVisibility::HIDDEN
        ) {
            $this->visibility =
                TableTokenVisibility::HIDDEN;
            ++$this->revision;
        }
    }

    public function id(): string
    {
        return $this->id;
    }

    public function tableId(): string
    {
        return $this->tableId;
    }

    public function sceneId(): string
    {
        return $this->sceneId;
    }

    public function label(): string
    {
        return $this->label;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function sourceReference(): ?string
    {
        return $this->sourceReference;
    }

    public function controllerUserId(): ?int
    {
        return $this->controllerUserId;
    }

    public function x(): float
    {
        return $this->x;
    }

    public function y(): float
    {
        return $this->y;
    }

    public function widthUnits(): float
    {
        return $this->widthUnits;
    }

    public function heightUnits(): float
    {
        return $this->heightUnits;
    }

    public function visibility(): string
    {
        return $this->visibility;
    }

    public function revision(): int
    {
        return $this->revision;
    }

    public function isVisible(): bool
    {
        return $this->visibility
            === TableTokenVisibility::VISIBLE;
    }

    /** @return array<string,mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'table_id' => $this->tableId,
            'scene_id' => $this->sceneId,
            'label' => $this->label,
            'type' => $this->type,
            'source_reference' => $this->sourceReference,
            'controller_user_id' => $this->controllerUserId,
            'x' => $this->x,
            'y' => $this->y,
            'width_units' => $this->widthUnits,
            'height_units' => $this->heightUnits,
            'visibility' => $this->visibility,
            'created_at' => $this->createdAt
                ->format(DATE_ATOM),
            'revision' => $this->revision,
        ];
    }

    private static function assertIdentity(
        string $id,
        string $tableId,
        string $sceneId,
        string $label
    ): void {
        if (
            trim($id) === ''
            || trim($tableId) === ''
            || trim($sceneId) === ''
            || trim($label) === ''
        ) {
            throw new InvalidArgumentException(
                'A Table token requires an ID, Table ID, Scene ID and label.'
            );
        }
    }

    private static function assertSourceReference(
        string $type,
        ?string $reference
    ): void {
        $reference = self::reference($reference);

        if (
            in_array(
                $type,
                [
                    TableTokenType::CHARACTER,
                    TableTokenType::CREATURE,
                ],
                true
            )
            && $reference === null
        ) {
            throw new InvalidArgumentException(
                'Character and Creature tokens require an opaque source reference.'
            );
        }
    }

    private static function assertController(
        ?int $controllerUserId
    ): void {
        if (
            $controllerUserId !== null
            && $controllerUserId < 1
        ) {
            throw new InvalidArgumentException(
                'A token controller must be a valid WordPress user ID.'
            );
        }
    }

    private static function assertCoordinates(
        float $x,
        float $y
    ): void {
        if (
            $x < 0
            || $x > 1
            || $y < 0
            || $y > 1
        ) {
            throw new InvalidArgumentException(
                'Table token coordinates must remain between 0 and 1.'
            );
        }
    }

    private static function assertFootprint(
        float $widthUnits,
        float $heightUnits
    ): void {
        if (
            $widthUnits <= 0
            || $heightUnits <= 0
        ) {
            throw new InvalidArgumentException(
                'A Table token footprint must be positive.'
            );
        }
    }

    private static function reference(
        ?string $reference
    ): ?string {
        $reference = $reference !== null
            ? trim($reference)
            : '';

        return $reference === ''
            ? null
            : $reference;
    }
}
