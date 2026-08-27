<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Battle\Models;

use InvalidArgumentException;

defined('ABSPATH') || exit;

final class CombatProfile
{
    public function __construct(
        private string $tokenId,
        private int $armorClass = 10,
        private int $attackModifier = 0,
        private int $attackRangeFeet = 5,
        private int $longRangeFeet = 5
    ) {
        if (trim($tokenId) === '') {
            throw new InvalidArgumentException(
                'A combat profile requires a token ID.'
            );
        }

        if ($armorClass < 1 || $armorClass > 50) {
            throw new InvalidArgumentException(
                'Armor Class must be between 1 and 50.'
            );
        }

        if ($attackModifier < -20 || $attackModifier > 30) {
            throw new InvalidArgumentException(
                'Attack modifier is outside the supported range.'
            );
        }

        if (
            $attackRangeFeet < 5
            || $attackRangeFeet > 1000
            || $attackRangeFeet % 5 !== 0
        ) {
            throw new InvalidArgumentException(
                'Attack range must be a 5-foot increment between 5 and 1000.'
            );
        }

        if (
            $longRangeFeet < $attackRangeFeet
            || $longRangeFeet > 2000
            || $longRangeFeet % 5 !== 0
        ) {
            throw new InvalidArgumentException(
                'Long range must be a 5-foot increment at least as large as normal range.'
            );
        }
    }

    public function tokenId(): string
    {
        return $this->tokenId;
    }

    public function armorClass(): int
    {
        return $this->armorClass;
    }

    public function attackModifier(): int
    {
        return $this->attackModifier;
    }

    public function attackRangeFeet(): int
    {
        return $this->attackRangeFeet;
    }

    public function longRangeFeet(): int
    {
        return $this->longRangeFeet;
    }

    public function isRangedAttack(): bool
    {
        return $this->attackRangeFeet > 5
            || $this->longRangeFeet > 5;
    }

    /** @return array<string,int|string> */
    public function toArray(): array
    {
        return [
            'token_id' => $this->tokenId,
            'armor_class' => $this->armorClass,
            'attack_modifier' => $this->attackModifier,
            'attack_range_feet' => $this->attackRangeFeet,
            'long_range_feet' => $this->longRangeFeet,
        ];
    }

    /** @param array<string,mixed> $record */
    public static function reconstitute(array $record): self
    {
        return new self(
            (string) ($record['token_id'] ?? ''),
            (int) ($record['armor_class'] ?? 10),
            (int) ($record['attack_modifier'] ?? 0),
            (int) ($record['attack_range_feet'] ?? 5),
            (int) ($record['long_range_feet'] ?? 5)
        );
    }
}
