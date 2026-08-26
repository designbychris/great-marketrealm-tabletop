<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tables\Services;

use DateTimeImmutable;
use GreatMarketrealmTabletop\Tables\Contracts\TableClock;

defined('ABSPATH') || exit;

final class SystemTableClock implements TableClock
{
    public function now(): DateTimeImmutable
    {
        return new DateTimeImmutable('now');
    }
}
