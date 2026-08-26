<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Battle\Models;

use GreatMarketrealmTabletop\Tabletop\Battle\Exceptions\TurnResourceSpent;

defined('ABSPATH') || exit;

final class TurnEconomy
{
    /** @var array<string,bool> */
    private array $spent;

    /** @param array<string,bool> $spent */
    public function __construct(array $spent = [])
    {
        $this->spent = [];

        foreach (TurnResource::all() as $resource) {
            $this->spent[$resource] = ! empty($spent[$resource]);
        }
    }

    public function spend(string $resource): void
    {
        $resource = TurnResource::assert($resource);

        if ($this->isSpent($resource)) {
            throw new TurnResourceSpent(
                'That turn resource has already been spent.'
            );
        }

        $this->spent[$resource] = true;
    }

    public function isSpent(string $resource): bool
    {
        $resource = TurnResource::assert($resource);

        return ! empty($this->spent[$resource]);
    }

    public function reset(): void
    {
        foreach (TurnResource::all() as $resource) {
            $this->spent[$resource] = false;
        }
    }

    /** @return array<string,bool> */
    public function toArray(): array
    {
        return $this->spent;
    }
}
