<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Battlefield\Models;

defined('ABSPATH') || exit;

final class BattlefieldDistance
{
    public function __construct(
        private int $squares,
        private int $feet
    ) {}

    public function squares(): int
    {
        return $this->squares;
    }

    public function feet(): int
    {
        return $this->feet;
    }

    public function adjacent(): bool
    {
        return $this->feet <= 5;
    }

    /** @return array<string,int|bool> */
    public function toArray(): array
    {
        return [
            'squares' => $this->squares,
            'feet' => $this->feet,
            'adjacent' => $this->adjacent(),
        ];
    }
}
