<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Battle\Models;

defined('ABSPATH') || exit;

final class DamageRoll
{
    /**
     * @param array<int,int> $rolls
     */
    public function __construct(
        private array $rolls,
        private int $modifier,
        private bool $critical
    ) {}

    /** @return array<int,int> */
    public function rolls(): array
    {
        return $this->rolls;
    }

    public function total(): int
    {
        return max(
            0,
            array_sum($this->rolls)
                + $this->modifier
        );
    }

    public function critical(): bool
    {
        return $this->critical;
    }

    /** @return array<string,mixed> */
    public function toArray(): array
    {
        return [
            'rolls' => $this->rolls,
            'modifier' => $this->modifier,
            'total' => $this->total(),
            'critical' => $this->critical,
        ];
    }
}
