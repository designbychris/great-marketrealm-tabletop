<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Battle\Models;

defined('ABSPATH') || exit;

final class AttackRangeAssessment
{
    public const NORMAL = 'in-range';
    public const LONG = 'long-range';
    public const OUT = 'out-of-range';

    public function __construct(
        private int $distanceFeet,
        private int $normalRangeFeet,
        private int $longRangeFeet,
        private string $status
    ) {}

    public function inRange(): bool
    {
        return $this->status !== self::OUT;
    }

    public function longRange(): bool
    {
        return $this->status === self::LONG;
    }

    public function distanceFeet(): int
    {
        return $this->distanceFeet;
    }

    public function status(): string
    {
        return $this->status;
    }

    /** @return array<string,int|string|bool> */
    public function toArray(): array
    {
        return [
            'distance_feet' => $this->distanceFeet,
            'normal_range_feet' => $this->normalRangeFeet,
            'long_range_feet' => $this->longRangeFeet,
            'range_status' => $this->status,
            'in_range' => $this->inRange(),
            'long_range' => $this->longRange(),
        ];
    }
}
