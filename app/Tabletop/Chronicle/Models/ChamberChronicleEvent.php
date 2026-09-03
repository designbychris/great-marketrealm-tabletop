<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Chronicle\Models;

use DateTimeImmutable;
use InvalidArgumentException;

defined('ABSPATH') || exit;

final class ChamberChronicleEvent
{
    public function __construct(
        private string $id,
        private string $tableId,
        private int $userId,
        private string $characterId,
        private string $characterName,
        private string $kind,
        private string $action,
        private string $summary,
        private DateTimeImmutable $occurredAt,
        private array $payload = [],
        private string $sessionId = ''
    ) {
        if (trim($id) === '' || trim($tableId) === '' || $userId < 1 || trim($characterId) === '' || trim($summary) === '') {
            throw new InvalidArgumentException('A Chamber Chronicle event requires stable Table, adventurer, and summary context.');
        }
    }

    /** @param array<string,mixed> $record */
    public static function reconstitute(array $record): self
    {
        return new self(
            (string) ($record['id'] ?? ''),
            (string) ($record['table_id'] ?? ''),
            (int) ($record['user_id'] ?? 0),
            (string) ($record['character_id'] ?? ''),
            (string) ($record['character_name'] ?? 'Adventurer'),
            (string) ($record['kind'] ?? 'satchel'),
            (string) ($record['action'] ?? 'roll'),
            (string) ($record['summary'] ?? ''),
            new DateTimeImmutable((string) ($record['occurred_at'] ?? 'now')),
            is_array($record['payload'] ?? null) ? $record['payload'] : [],
            (string) ($record['session_id'] ?? '')
        );
    }

    public function sessionId(): string { return $this->sessionId; }

    public function withSessionId(string $sessionId): self
    {
        return new self($this->id, $this->tableId, $this->userId, $this->characterId, $this->characterName, $this->kind, $this->action, $this->summary, $this->occurredAt, $this->payload, trim($sessionId));
    }

    /** @return array<string,mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'table_id' => $this->tableId,
            'user_id' => $this->userId,
            'character_id' => $this->characterId,
            'character_name' => $this->characterName,
            'kind' => $this->kind,
            'action' => $this->action,
            'summary' => $this->summary,
            'occurred_at' => $this->occurredAt->format(DATE_ATOM),
            'payload' => $this->payload,
            'session_id' => $this->sessionId,
        ];
    }
}
