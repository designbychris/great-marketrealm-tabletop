<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Encounters\Models;

use InvalidArgumentException;

defined('ABSPATH') || exit;

final class EncounterCombatant
{
    public function __construct(
        private string $tokenId,
        private int $initiative,
        private int $initiativeModifier = 0
    ) {
        if (trim($tokenId) === '') {
            throw new InvalidArgumentException(
                'An Encounter combatant requires a token ID.'
            );
        }

        $this->tokenId = trim($tokenId);
    }

    /** @param array<string,mixed> $record */
    public static function reconstitute(array $record): self
    {
        return new self(
            (string) ($record['token_id'] ?? ''),
            (int) ($record['initiative'] ?? 0),
            (int) ($record['initiative_modifier'] ?? 0)
        );
    }

    public function tokenId(): string
    {
        return $this->tokenId;
    }

    public function initiative(): int
    {
        return $this->initiative;
    }

    public function initiativeModifier(): int
    {
        return $this->initiativeModifier;
    }

    /** @return array<string,mixed> */
    public function toArray(): array
    {
        return [
            'token_id' => $this->tokenId,
            'initiative' => $this->initiative,
            'initiative_modifier' => $this->initiativeModifier,
        ];
    }
}
