<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tables\Scenes\Models;

use DateTimeImmutable;
use InvalidArgumentException;

defined('ABSPATH') || exit;

final class TableScene
{
    private function __construct(
        private string $id,
        private string $tableId,
        private string $name,
        private int $mapAttachmentId,
        private int $width,
        private int $height,
        private string $gridType,
        private int $gridSize,
        private bool $active,
        private DateTimeImmutable $createdAt
    ) {}

    public static function create(
        string $id,
        string $tableId,
        string $name,
        int $mapAttachmentId,
        int $width,
        int $height,
        string $gridType,
        int $gridSize,
        DateTimeImmutable $createdAt
    ): self {
        self::assertIdentity($id, $tableId, $name);
        self::assertSurface($mapAttachmentId, $width, $height);
        $gridType = GridType::assert($gridType);
        self::assertGrid($gridType, $gridSize);

        return new self(
            trim($id),
            trim($tableId),
            trim($name),
            $mapAttachmentId,
            $width,
            $height,
            $gridType,
            $gridSize,
            false,
            $createdAt
        );
    }

    /** @param array<string,mixed> $record */
    public static function reconstitute(array $record): self
    {
        $scene = self::create(
            (string) ($record['id'] ?? ''),
            (string) ($record['table_id'] ?? ''),
            (string) ($record['name'] ?? ''),
            (int) ($record['map_attachment_id'] ?? 0),
            (int) ($record['width'] ?? 0),
            (int) ($record['height'] ?? 0),
            (string) ($record['grid_type'] ?? ''),
            (int) ($record['grid_size'] ?? 0),
            new DateTimeImmutable((string) ($record['created_at'] ?? 'now'))
        );

        if (! empty($record['active'])) {
            $scene->activate();
        }

        return $scene;
    }

    public function activate(): void { $this->active = true; }
    public function deactivate(): void { $this->active = false; }

    public function replaceMap(
        int $mapAttachmentId,
        int $width,
        int $height
    ): void {
        self::assertSurface(
            $mapAttachmentId,
            $width,
            $height
        );

        $this->mapAttachmentId = $mapAttachmentId;
        $this->width = $width;
        $this->height = $height;
    }

    public function id(): string { return $this->id; }
    public function tableId(): string { return $this->tableId; }
    public function name(): string { return $this->name; }
    public function mapAttachmentId(): int { return $this->mapAttachmentId; }
    public function width(): int { return $this->width; }
    public function height(): int { return $this->height; }
    public function gridType(): string { return $this->gridType; }
    public function gridSize(): int { return $this->gridSize; }
    public function isActive(): bool { return $this->active; }
    public function createdAt(): DateTimeImmutable { return $this->createdAt; }

    /**
     * Normalised coordinates keep later tokens independent of image pixels.
     *
     * @return array{x:float,y:float}
     */
    public function coordinates(float $x, float $y): array
    {
        if ($x < 0 || $x > 1 || $y < 0 || $y > 1) {
            throw new InvalidArgumentException(
                'Battlemap coordinates must remain between 0 and 1.'
            );
        }

        return ['x' => $x, 'y' => $y];
    }

    /** @return array<string,mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'table_id' => $this->tableId,
            'name' => $this->name,
            'map_attachment_id' => $this->mapAttachmentId,
            'width' => $this->width,
            'height' => $this->height,
            'grid_type' => $this->gridType,
            'grid_size' => $this->gridSize,
            'active' => $this->active,
            'created_at' => $this->createdAt->format(DATE_ATOM),
        ];
    }

    private static function assertIdentity(
        string $id,
        string $tableId,
        string $name
    ): void {
        if (trim($id) === '' || trim($tableId) === '' || trim($name) === '') {
            throw new InvalidArgumentException(
                'A battlemap scene requires an ID, Table ID and name.'
            );
        }
    }

    private static function assertSurface(
        int $attachmentId,
        int $width,
        int $height
    ): void {
        if ($attachmentId < 1) {
            throw new InvalidArgumentException(
                'A battlemap requires a WordPress Media attachment ID.'
            );
        }
        if ($width < 1 || $height < 1) {
            throw new InvalidArgumentException(
                'A battlemap requires positive surface dimensions.'
            );
        }
    }

    private static function assertGrid(string $type, int $size): void
    {
        if ($type === GridType::SQUARE && $size < 1) {
            throw new InvalidArgumentException(
                'A square grid requires a positive grid size.'
            );
        }
        if ($type === GridType::NONE && $size !== 0) {
            throw new InvalidArgumentException(
                'A gridless battlemap must use a grid size of zero.'
            );
        }
    }
}
