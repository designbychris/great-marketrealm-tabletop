<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tables\Contracts;

defined('ABSPATH') || exit;

interface TableCapacityPolicy
{
    public function limit(): int;
}
