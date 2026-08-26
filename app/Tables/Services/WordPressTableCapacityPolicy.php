<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tables\Services;

use GreatMarketrealmTabletop\Tables\Contracts\TableCapacityPolicy;

defined('ABSPATH') || exit;

final class WordPressTableCapacityPolicy implements TableCapacityPolicy
{
    public function limit(): int
    {
        return max(
            1,
            (int) get_option('gmrt_active_table_capacity', 2)
        );
    }
}
