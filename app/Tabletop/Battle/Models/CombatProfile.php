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
        private int $attackModifier = 0
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

    /** @return array<string,int|string> */
    public function toArray(): array
    {
        return [
            'token_id' => $this->tokenId,
            'armor_class' => $this->armorClass,
            'attack_modifier' => $this->attackModifier,
        ];
    }

    /** @param array<string,mixed> $record */
    public static function reconstitute(array $record): self
    {
        return new self(
            (string) ($record['token_id'] ?? ''),
            (int) ($record['armor_class'] ?? 10),
            (int) ($record['attack_modifier'] ?? 0)
        );
    }
}
