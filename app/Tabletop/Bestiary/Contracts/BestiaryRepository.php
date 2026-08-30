<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Bestiary\Contracts;

use GreatMarketrealmTabletop\Tabletop\Bestiary\Models\BestiaryCreature;

defined('ABSPATH') || exit;

interface BestiaryRepository
{
    /** @return array<int,BestiaryCreature> */
    public function all(): array;

    public function find(string $id): ?BestiaryCreature;
}
