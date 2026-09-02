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
        private string $description,
        private string $status,
        private DateTimeImmutable $createdAt,
        private ?DateTimeImmutable $activatedAt = null,
        private ?DateTimeImmutable $endedAt = null,
        private ?DateTimeImmutable $lastHeartbeatAt = null,
        private ?DateTimeImmutable $leaseExpiresAt = null
    ) {}

    public static function prepare(
        string $id,
        int $dungeonMasterUserId,
        string $name,
        DateTimeImmutable $createdAt,
        string $description = ''
    ): self {
        $id = trim($id);
        $name = trim($name);
        $description = trim($description);

        if ($id === '') {
            throw new InvalidArgumentException('A Table requires a stable identity.');
        }
        if ($dungeonMasterUserId < 1) {
            throw new InvalidArgumentException('A Table requires a Dungeon Master user ID.');
        }
        if ($name === '') {
            throw new InvalidArgumentException('A Table requires a name.');
        }

        return new self(
            $id,
            $dungeonMasterUserId,
            $name,
            $description,
            TableStatus::PREPARING,
            $createdAt
        );
    }

    /** @param array<string,mixed> $record */
    public static function reconstitute(array $record): self
    {
        return new self(
            (string) ($record['id'] ?? ''),
            (int) ($record['dungeon_master_user_id'] ?? 0),
            (string) ($record['name'] ?? ''),
            (string) ($record['description'] ?? ''),
            TableStatus::assert((string) ($record['status'] ?? '')),
            new DateTimeImmutable((string) ($record['created_at'] ?? 'now')),
            self::date($record['activated_at'] ?? null),
            self::date($record['ended_at'] ?? null),
            self::date($record['last_heartbeat_at'] ?? null),
            self::date($record['lease_expires_at'] ?? null)
        );
    }

    public function activate(
        DateTimeImmutable $when,
        ?DateTimeImmutable $leaseExpiresAt = null
    ): void {
        if ($this->status !== TableStatus::PREPARING) {
            throw new InvalidTableTransition('Only a preparing Table may become active.');
        }

        $this->status = TableStatus::ACTIVE;
        $this->activatedAt = $when;
        $this->lastHeartbeatAt = $when;
        $this->leaseExpiresAt = $leaseExpiresAt ?? $when->modify('+17 minutes');
    }

    public function heartbeat(
        DateTimeImmutable $when,
        DateTimeImmutable $leaseExpiresAt
    ): void {
        if ($this->status !== TableStatus::ACTIVE) {
            throw new InvalidTableTransition('Only an active Table may receive a heartbeat.');
        }

        $this->lastHeartbeatAt = $when;
        $this->leaseExpiresAt = $leaseExpiresAt;
    }

    public function end(DateTimeImmutable $when): void
    {
        if ($this->status !== TableStatus::ACTIVE) {
            throw new InvalidTableTransition('Only an active Table may end.');
        }

        $this->status = TableStatus::ENDED;
        $this->endedAt = $when;
    }

    public function expire(DateTimeImmutable $when): void
    {
        $this->end($when);
    }

    public function id(): string { return $this->id; }
    public function dungeonMasterUserId(): int { return $this->dungeonMasterUserId; }
    public function name(): string { return $this->name; }
    public function description(): string { return $this->description; }
    public function status(): string { return $this->status; }
    public function createdAt(): DateTimeImmutable { return $this->createdAt; }
    public function activatedAt(): ?DateTimeImmutable { return $this->activatedAt; }
    public function endedAt(): ?DateTimeImmutable { return $this->endedAt; }
    public function lastHeartbeatAt(): ?DateTimeImmutable { return $this->lastHeartbeatAt; }
    public function leaseExpiresAt(): ?DateTimeImmutable { return $this->leaseExpiresAt; }

    public function leaseExpired(DateTimeImmutable $now): bool
    {
        return $this->isActive()
            && $this->leaseExpiresAt !== null
            && $this->leaseExpiresAt <= $now;
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
            'description' => $this->description,
            'status' => $this->status,
            'created_at' => $this->createdAt->format(DATE_ATOM),
            'activated_at' => $this->activatedAt?->format(DATE_ATOM),
            'ended_at' => $this->endedAt?->format(DATE_ATOM),
            'last_heartbeat_at' => $this->lastHeartbeatAt?->format(DATE_ATOM),
            'lease_expires_at' => $this->leaseExpiresAt?->format(DATE_ATOM),
        ];
    }

    private static function date(mixed $value): ?DateTimeImmutable
    {
        $value = is_string($value) ? trim($value) : '';
        return $value === '' ? null : new DateTimeImmutable($value);
    }
}
