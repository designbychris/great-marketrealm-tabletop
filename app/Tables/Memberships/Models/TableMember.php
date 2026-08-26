<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tables\Memberships\Models;

use DateTimeImmutable;
use GreatMarketrealmTabletop\Tables\Memberships\Exceptions\TableMembershipException;
use InvalidArgumentException;

defined('ABSPATH') || exit;

final class TableMember
{
    private function __construct(
        private string $tableId,
        private int $userId,
        private string $role,
        private string $status,
        private DateTimeImmutable $invitedAt,
        private ?DateTimeImmutable $joinedAt = null,
        private ?DateTimeImmutable $leftAt = null,
        private ?string $companionCharacterId = null
    ) {}

    public static function dungeonMaster(
        string $tableId,
        int $userId,
        DateTimeImmutable $when
    ): self {
        self::assertIdentity($tableId, $userId);

        return new self(
            trim($tableId),
            $userId,
            TableMemberRole::DUNGEON_MASTER,
            TableMemberStatus::ACTIVE,
            $when,
            $when
        );
    }

    public static function invitePlayer(
        string $tableId,
        int $userId,
        DateTimeImmutable $when
    ): self {
        self::assertIdentity($tableId, $userId);

        return new self(
            trim($tableId),
            $userId,
            TableMemberRole::PLAYER,
            TableMemberStatus::INVITED,
            $when
        );
    }

    /** @param array<string,mixed> $record */
    public static function reconstitute(array $record): self
    {
        return new self(
            trim((string) ($record['table_id'] ?? '')),
            (int) ($record['user_id'] ?? 0),
            TableMemberRole::assert(
                (string) ($record['role'] ?? '')
            ),
            TableMemberStatus::assert(
                (string) ($record['status'] ?? '')
            ),
            new DateTimeImmutable(
                (string) ($record['invited_at'] ?? 'now')
            ),
            self::date($record['joined_at'] ?? null),
            self::date($record['left_at'] ?? null),
            self::characterId(
                $record['companion_character_id'] ?? null
            )
        );
    }

    public function join(DateTimeImmutable $when): void
    {
        if (
            $this->role !== TableMemberRole::PLAYER
            || $this->status !== TableMemberStatus::INVITED
        ) {
            throw new TableMembershipException(
                'Only an invited player may join a Table.'
            );
        }

        $this->status = TableMemberStatus::ACTIVE;
        $this->joinedAt = $when;
        $this->leftAt = null;
    }

    public function leave(DateTimeImmutable $when): void
    {
        if ($this->role === TableMemberRole::DUNGEON_MASTER) {
            throw new TableMembershipException(
                'The Dungeon Master cannot leave their own Table.'
            );
        }

        if ($this->status !== TableMemberStatus::ACTIVE) {
            throw new TableMembershipException(
                'Only an active player may leave a Table.'
            );
        }

        $this->status = TableMemberStatus::LEFT;
        $this->leftAt = $when;
        $this->companionCharacterId = null;
    }

    public function selectCompanionCharacter(
        string $characterId
    ): void {
        if ($this->status !== TableMemberStatus::ACTIVE) {
            throw new TableMembershipException(
                'Only an active Table member may select a Character.'
            );
        }

        $characterId = trim($characterId);

        if ($characterId === '') {
            throw new InvalidArgumentException(
                'A Companion Character reference cannot be empty.'
            );
        }

        $this->companionCharacterId = $characterId;
    }

    public function clearCompanionCharacter(): void
    {
        $this->companionCharacterId = null;
    }

    public function tableId(): string
    {
        return $this->tableId;
    }

    public function userId(): int
    {
        return $this->userId;
    }

    public function role(): string
    {
        return $this->role;
    }

    public function status(): string
    {
        return $this->status;
    }

    public function companionCharacterId(): ?string
    {
        return $this->companionCharacterId;
    }

    public function isDungeonMaster(): bool
    {
        return $this->role === TableMemberRole::DUNGEON_MASTER;
    }

    public function isActive(): bool
    {
        return $this->status === TableMemberStatus::ACTIVE;
    }

    /** @return array<string,mixed> */
    public function toArray(): array
    {
        return [
            'table_id' => $this->tableId,
            'user_id' => $this->userId,
            'role' => $this->role,
            'status' => $this->status,
            'invited_at' => $this->invitedAt->format(DATE_ATOM),
            'joined_at' => $this->joinedAt?->format(DATE_ATOM),
            'left_at' => $this->leftAt?->format(DATE_ATOM),
            'companion_character_id' => $this->companionCharacterId,
        ];
    }

    private static function assertIdentity(
        string $tableId,
        int $userId
    ): void {
        if (trim($tableId) === '') {
            throw new InvalidArgumentException(
                'A Table member requires a Table ID.'
            );
        }

        if ($userId < 1) {
            throw new InvalidArgumentException(
                'A Table member requires a WordPress user ID.'
            );
        }
    }

    private static function date(
        mixed $value
    ): ?DateTimeImmutable {
        $value = is_string($value)
            ? trim($value)
            : '';

        return $value === ''
            ? null
            : new DateTimeImmutable($value);
    }

    private static function characterId(
        mixed $value
    ): ?string {
        $value = is_string($value)
            ? trim($value)
            : '';

        return $value === ''
            ? null
            : $value;
    }
}
