<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Battle\Models;

use InvalidArgumentException;

defined('ABSPATH') || exit;

final class DamageProfile
{
    public function __construct(
        private string $tokenId,
        private int $diceCount = 1,
        private int $dieSides = 6,
        private int $modifier = 0,
        private string $damageType = DamageType::SLASHING
    ) {
        if (trim($tokenId) === '') {
            throw new InvalidArgumentException(
                'A damage profile requires a token ID.'
            );
        }

        if ($diceCount < 1 || $diceCount > 20) {
            throw new InvalidArgumentException(
                'Damage dice count must be between 1 and 20.'
            );
        }

        if (
            ! in_array(
                $dieSides,
                [4, 6, 8, 10, 12, 20],
                true
            )
        ) {
            throw new InvalidArgumentException(
                'Unsupported damage die.'
            );
        }

        if ($modifier < -50 || $modifier > 100) {
            throw new InvalidArgumentException(
                'Damage modifier is outside the supported range.'
            );
        }

        DamageType::assert($damageType);
    }

    public function tokenId(): string
    {
        return $this->tokenId;
    }

    public function diceCount(): int
    {
        return $this->diceCount;
    }

    public function dieSides(): int
    {
        return $this->dieSides;
    }

    public function modifier(): int
    {
        return $this->modifier;
    }

    public function damageType(): string
    {
        return $this->damageType;
    }

    /** @return array<string,int|string> */
    public function toArray(): array
    {
        return [
            'token_id' => $this->tokenId,
            'dice_count' => $this->diceCount,
            'die_sides' => $this->dieSides,
            'modifier' => $this->modifier,
            'damage_type' => $this->damageType,
        ];
    }

    /** @param array<string,mixed> $record */
    public static function reconstitute(array $record): self
    {
        return new self(
            (string) ($record['token_id'] ?? ''),
            (int) ($record['dice_count'] ?? 1),
            (int) ($record['die_sides'] ?? 6),
            (int) ($record['modifier'] ?? 0),
            (string) (
                $record['damage_type']
                ?? DamageType::SLASHING
            )
        );
    }
}
