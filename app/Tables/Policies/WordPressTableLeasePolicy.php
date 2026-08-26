<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tables\Policies;

use GreatMarketrealmTabletop\Tables\Contracts\TableLeasePolicy;

defined('ABSPATH') || exit;

final class WordPressTableLeasePolicy implements TableLeasePolicy
{
    public function leaseSeconds(): int
    {
        return max(300, (int) get_option('gmrt_table_lease_seconds', 900));
    }

    public function heartbeatGraceSeconds(): int
    {
        return max(60, (int) get_option('gmrt_table_heartbeat_grace_seconds', 120));
    }
}
