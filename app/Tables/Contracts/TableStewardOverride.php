<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tables\Contracts;

defined('ABSPATH') || exit;

interface TableStewardOverride
{
    public function mayBypassCapacity(int $dungeonMasterUserId): bool;
}
