<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Sessions\Models;

use DateTimeImmutable;

final class SessionRecap
{
    public function __construct(
        private string $sessionId,
        private string $tableId,
        private string $draft,
        private DateTimeImmutable $generatedAt,
        private bool $keeperEdited = false
    ) {}

    /** @param array<string,mixed> $record */
    public static function reconstitute(array $record): self
    {
        return new self(
            (string) ($record['session_id'] ?? ''),
            (string) ($record['table_id'] ?? ''),
            (string) ($record['draft'] ?? ''),
            new DateTimeImmutable((string) ($record['generated_at'] ?? 'now')),
            (bool) ($record['keeper_edited'] ?? false)
        );
    }

    public function sessionId(): string { return $this->sessionId; }
    public function tableId(): string { return $this->tableId; }
    public function draft(): string { return $this->draft; }
    public function keeperEdited(): bool { return $this->keeperEdited; }

    public function revise(string $draft): void
    {
        $this->draft = trim($draft);
        $this->keeperEdited = true;
    }

    /** @return array<string,mixed> */
    public function toArray(): array
    {
        return [
            'session_id' => $this->sessionId,
            'table_id' => $this->tableId,
            'draft' => $this->draft,
            'generated_at' => $this->generatedAt->format(DATE_ATOM),
            'keeper_edited' => $this->keeperEdited,
        ];
    }
}
