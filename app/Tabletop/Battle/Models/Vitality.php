<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Battle\Models;

use InvalidArgumentException;

defined('ABSPATH') || exit;

final class Vitality
{
    public function __construct(
        private string $tokenId,
        private int $maximumHp,
        private int $currentHp,
        private int $temporaryHp = 0
    ) {
        if (trim($tokenId) === '') {
            throw new InvalidArgumentException(
                'Vitality requires a token ID.'
            );
        }

        if ($maximumHp < 1 || $maximumHp > 100000) {
            throw new InvalidArgumentException(
                'Maximum HP must be between 1 and 100000.'
            );
        }

        if ($currentHp < 0 || $currentHp > $maximumHp) {
            throw new InvalidArgumentException(
                'Current HP must be between 0 and Maximum HP.'
            );
        }

        if ($temporaryHp < 0 || $temporaryHp > 100000) {
            throw new InvalidArgumentException(
                'Temporary HP must be zero or greater.'
            );
        }
    }

    public function tokenId(): string
    {
        return $this->tokenId;
    }

    public function maximumHp(): int
    {
        return $this->maximumHp;
    }

    public function currentHp(): int
    {
        return $this->currentHp;
    }

    public function temporaryHp(): int
    {
        return $this->temporaryHp;
    }

    /**
     * @return array{
     *   incoming:int,
     *   temporary_absorbed:int,
     *   hp_lost:int,
     *   current_hp:int,
     *   temporary_hp:int,
     *   state:string
     * }
     */
    public function damage(int $amount): array
    {
        if ($amount < 0) {
            throw new InvalidArgumentException(
                'Damage cannot be negative.'
            );
        }

        $incoming = $amount;
        $absorbed = min($this->temporaryHp, $amount);
        $this->temporaryHp -= $absorbed;
        $amount -= $absorbed;

        $hpLost = min($this->currentHp, $amount);
        $this->currentHp -= $hpLost;

        return [
            'incoming' => $incoming,
            'temporary_absorbed' => $absorbed,
            'hp_lost' => $hpLost,
            'current_hp' => $this->currentHp,
            'temporary_hp' => $this->temporaryHp,
            'state' => $this->state(),
        ];
    }

    public function heal(int $amount): int
    {
        if ($amount < 0) {
            throw new InvalidArgumentException(
                'Healing cannot be negative.'
            );
        }

        $before = $this->currentHp;

        $this->currentHp = min(
            $this->maximumHp,
            $this->currentHp + $amount
        );

        return $this->currentHp - $before;
    }

    public function grantTemporaryHp(int $amount): void
    {
        if ($amount < 0) {
            throw new InvalidArgumentException(
                'Temporary HP cannot be negative.'
            );
        }

        $this->temporaryHp = max(
            $this->temporaryHp,
            $amount
        );
    }

    public function state(): string
    {
        if ($this->currentHp === 0) {
            return VitalityState::DOWN;
        }

        return $this->currentHp === $this->maximumHp
            ? VitalityState::HEALTHY
            : VitalityState::WOUNDED;
    }

    public function percentage(): float
    {
        return ($this->currentHp / $this->maximumHp) * 100;
    }

    /** @return array<string,int|string|float> */
    public function toArray(): array
    {
        return [
            'token_id' => $this->tokenId,
            'maximum_hp' => $this->maximumHp,
            'current_hp' => $this->currentHp,
            'temporary_hp' => $this->temporaryHp,
            'state' => $this->state(),
            'percentage' => $this->percentage(),
        ];
    }

    /** @param array<string,mixed> $record */
    public static function reconstitute(array $record): self
    {
        return new self(
            (string) ($record['token_id'] ?? ''),
            (int) ($record['maximum_hp'] ?? 1),
            (int) ($record['current_hp'] ?? 1),
            (int) ($record['temporary_hp'] ?? 0)
        );
    }
}
