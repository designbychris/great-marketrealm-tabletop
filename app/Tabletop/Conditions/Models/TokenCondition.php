<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Conditions\Models;

use DateTimeImmutable;
use InvalidArgumentException;

defined('ABSPATH') || exit;

final class TokenCondition
{
    public function __construct(
        private string $tokenId,
        private string $condition,
        private ?int $turnsRemaining,
        private DateTimeImmutable $appliedAt
    ) {
        if (trim($tokenId) === '') {
            throw new InvalidArgumentException(
                'A condition requires a token ID.'
            );
        }

        ConditionType::assert($condition);

        if ($turnsRemaining !== null && $turnsRemaining < 1) {
            throw new InvalidArgumentException(
                'Condition duration must be at least one turn.'
            );
        }
    }

    public function tokenId(): string { return $this->tokenId; }
    public function condition(): string { return $this->condition; }
    public function turnsRemaining(): ?int { return $this->turnsRemaining; }

    public function afterTurn(): ?self
    {
        if ($this->turnsRemaining === null) {
            return $this;
        }

        if ($this->turnsRemaining <= 1) {
            return null;
        }

        return new self(
            $this->tokenId,
            $this->condition,
            $this->turnsRemaining - 1,
            $this->appliedAt
        );
    }

    /** @return array<string,mixed> */
    public function toArray(): array
    {
        return [
            'token_id' => $this->tokenId,
            'condition' => $this->condition,
            'turns_remaining' => $this->turnsRemaining,
            'applied_at' => $this->appliedAt->format(DATE_ATOM),
        ];
    }

    /** @param array<string,mixed> $record */
    public static function reconstitute(array $record): self
    {
        return new self(
            (string) ($record['token_id'] ?? ''),
            (string) ($record['condition'] ?? ''),
            isset($record['turns_remaining'])
                ? (int) $record['turns_remaining']
                : null,
            new DateTimeImmutable(
                (string) ($record['applied_at'] ?? 'now')
            )
        );
    }
}
