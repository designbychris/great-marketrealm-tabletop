<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Battle\Models;

use DateTimeImmutable;
use InvalidArgumentException;

defined('ABSPATH') || exit;

final class BattleEvent
{
    public function __construct(
        private string $id,
        private string $tableId,
        private string $encounterId,
        private string $type,
        private string $tokenId,
        private int $round,
        private int $turnIndex,
        private DateTimeImmutable $occurredAt,
        private array $payload = []
    ) {
        foreach (
            [$id, $tableId, $encounterId, $type, $tokenId]
            as $value
        ) {
            if (trim($value) === '') {
                throw new InvalidArgumentException(
                    'A battle event requires stable identity and token context.'
                );
            }
        }
    }

    /** @param array<string,mixed> $record */
    public static function reconstitute(array $record): self
    {
        return new self(
            (string) ($record['id'] ?? ''),
            (string) ($record['table_id'] ?? ''),
            (string) ($record['encounter_id'] ?? ''),
            (string) ($record['type'] ?? ''),
            (string) ($record['token_id'] ?? ''),
            (int) ($record['round'] ?? 0),
            (int) ($record['turn_index'] ?? 0),
            new DateTimeImmutable(
                (string) ($record['occurred_at'] ?? 'now')
            ),
            is_array($record['payload'] ?? null)
                ? $record['payload']
                : []
        );
    }

    public function encounterId(): string
    {
        return $this->encounterId;
    }

    /** @return array<string,mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'table_id' => $this->tableId,
            'encounter_id' => $this->encounterId,
            'type' => $this->type,
            'token_id' => $this->tokenId,
            'round' => $this->round,
            'turn_index' => $this->turnIndex,
            'occurred_at' => $this->occurredAt->format(DATE_ATOM),
            'payload' => $this->payload,
        ];
    }
}
