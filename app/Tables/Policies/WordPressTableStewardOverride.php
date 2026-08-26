<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tables\Policies;

use GreatMarketrealmTabletop\Tables\Contracts\TableStewardOverride;

defined('ABSPATH') || exit;

final class WordPressTableStewardOverride implements TableStewardOverride
{
    public function mayBypassCapacity(int $dungeonMasterUserId): bool
    {
        $ids = get_option('gmrt_capacity_override_user_ids', []);
        if (! is_array($ids)) {
            return false;
        }

        return in_array($dungeonMasterUserId, array_map('intval', $ids), true);
    }
}
