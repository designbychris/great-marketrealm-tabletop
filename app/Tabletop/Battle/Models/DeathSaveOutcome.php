<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Battle\Models;

defined('ABSPATH') || exit;

final class DeathSaveOutcome
{
    public const NATURAL_TWENTY = 'natural-twenty';
    public const SUCCESS = 'success';
    public const FAILURE = 'failure';
    public const NATURAL_ONE = 'natural-one';

    public function __construct(
        private int $roll
    ) {}

    public function roll(): int
    {
        return $this->roll;
    }

    public function result(): string
    {
        if ($this->roll === 20) {
            return self::NATURAL_TWENTY;
        }

        if ($this->roll === 1) {
            return self::NATURAL_ONE;
        }

        return $this->roll >= 10
            ? self::SUCCESS
            : self::FAILURE;
    }

    public function successes(): int
    {
        return in_array(
            $this->result(),
            [self::SUCCESS],
            true
        ) ? 1 : 0;
    }

    public function failures(): int
    {
        return match ($this->result()) {
            self::NATURAL_ONE => 2,
            self::FAILURE => 1,
            default => 0,
        };
    }

    public function revives(): bool
    {
        return $this->result() === self::NATURAL_TWENTY;
    }

    /** @return array<string,int|string|bool> */
    public function toArray(): array
    {
        return [
            'roll' => $this->roll,
            'result' => $this->result(),
            'successes' => $this->successes(),
            'failures' => $this->failures(),
            'revives' => $this->revives(),
        ];
    }
}
