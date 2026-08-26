<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tables\Contracts;

use DateTimeImmutable;

defined('ABSPATH') || exit;

interface TableClock
{
    public function now(): DateTimeImmutable;
}
