<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Sessions\Models;

use DateTimeImmutable;
use RuntimeException;

defined('ABSPATH') || exit;

final class TableSession
{
    public function __construct(
        private string $id,
        private string $tableId,
        private int $number,
        private string $title,
        private string $status,
        private DateTimeImmutable $startedAt,
        private ?DateTimeImmutable $endedAt = null
    ) {
        if ($id === '' || $tableId === '' || $number < 1) {
            throw new RuntimeException('A Table Session requires an ID, Table, and positive Session number.');
        }
    }

    /** @param array<string,mixed> $record */
    public static function reconstitute(array $record): self
    {
        return new self(
            (string) ($record['id'] ?? ''),
            (string) ($record['table_id'] ?? ''),
            max(1, (int) ($record['number'] ?? 1)),
            (string) ($record['title'] ?? ''),
            (string) ($record['status'] ?? TableSessionStatus::ENDED),
            new DateTimeImmutable((string) ($record['started_at'] ?? 'now')),
            ! empty($record['ended_at']) ? new DateTimeImmutable((string) $record['ended_at']) : null
        );
    }

    public function end(DateTimeImmutable $when): void
    {
        if (! $this->isActive()) {
            throw new RuntimeException('Only the current Session may be ended.');
        }

        $this->status = TableSessionStatus::ENDED;
        $this->endedAt = $when;
    }

    public function id(): string { return $this->id; }
    public function tableId(): string { return $this->tableId; }
    public function number(): int { return $this->number; }
    public function title(): string { return $this->title; }
    public function status(): string { return $this->status; }
    public function startedAt(): DateTimeImmutable { return $this->startedAt; }
    public function endedAt(): ?DateTimeImmutable { return $this->endedAt; }
    public function isActive(): bool { return $this->status === TableSessionStatus::ACTIVE; }

    /** @return array<string,mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'table_id' => $this->tableId,
            'number' => $this->number,
            'title' => $this->title,
            'status' => $this->status,
            'started_at' => $this->startedAt->format(DATE_ATOM),
            'ended_at' => $this->endedAt?->format(DATE_ATOM),
        ];
    }
}
