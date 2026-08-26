<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tables\Models;

use DateTimeImmutable;
use GreatMarketrealmTabletop\Tables\Exceptions\InvalidTableTransition;
use InvalidArgumentException;

defined('ABSPATH') || exit;

final class Table
{
    private function __construct(
        private string $id,
        private int $dungeonMasterUserId,
        private string $name,
        private string $status,
        private DateTimeImmutable $createdAt,
        private ?DateTimeImmutable $activatedAt = null,
        private ?DateTimeImmutable $endedAt = null
    ) {}

    public static function prepare(
        string $id,
        int $dungeonMasterUserId,
        string $name,
        DateTimeImmutable $createdAt
    ): self {
        $id = trim($id);
        $name = trim($name);

        if ($id === '') {
            throw new InvalidArgumentException(
                'A Table requires a stable identity.'
            );
        }

        if ($dungeonMasterUserId < 1) {
            throw new InvalidArgumentException(
                'A Table requires a Dungeon Master user ID.'
            );
        }

        if ($name === '') {
            throw new InvalidArgumentException(
                'A Table requires a name.'
            );
        }

        return new self(
            $id,
            $dungeonMasterUserId,
            $name,
            TableStatus::PREPARING,
            $createdAt
        );
    }

    /** @param array<string,mixed> $record */
    public static function reconstitute(array $record): self
    {
        $status = TableStatus::assert(
            (string) ($record['status'] ?? '')
        );

        return new self(
            (string) ($record['id'] ?? ''),
            (int) ($record['dungeon_master_user_id'] ?? 0),
            (string) ($record['name'] ?? ''),
            $status,
            new DateTimeImmutable((string) ($record['created_at'] ?? 'now')),
            self::date($record['activated_at'] ?? null),
            self::date($record['ended_at'] ?? null)
        );
    }

    public function activate(DateTimeImmutable $when): void
    {
        if ($this->status !== TableStatus::PREPARING) {
            throw new InvalidTableTransition(
                'Only a preparing Table may become active.'
            );
        }

        $this->status = TableStatus::ACTIVE;
        $this->activatedAt = $when;
    }

    public function end(DateTimeImmutable $when): void
    {
        if ($this->status !== TableStatus::ACTIVE) {
            throw new InvalidTableTransition(
                'Only an active Table may end.'
            );
        }

        $this->status = TableStatus::ENDED;
        $this->endedAt = $when;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function dungeonMasterUserId(): int
    {
        return $this->dungeonMasterUserId;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function status(): string
    {
        return $this->status;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function activatedAt(): ?DateTimeImmutable
    {
        return $this->activatedAt;
    }

    public function endedAt(): ?DateTimeImmutable
    {
        return $this->endedAt;
    }

    public function isActive(): bool
    {
        return $this->status === TableStatus::ACTIVE;
    }

    /** @return array<string,mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'dungeon_master_user_id' => $this->dungeonMasterUserId,
            'name' => $this->name,
            'status' => $this->status,
            'created_at' => $this->createdAt->format(DATE_ATOM),
            'activated_at' => $this->activatedAt?->format(DATE_ATOM),
            'ended_at' => $this->endedAt?->format(DATE_ATOM),
        ];
    }

    private static function date(mixed $value): ?DateTimeImmutable
    {
        $value = is_string($value) ? trim($value) : '';

        return $value === ''
            ? null
            : new DateTimeImmutable($value);
    }
}
