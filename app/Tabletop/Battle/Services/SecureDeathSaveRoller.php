<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Battle\Services;

use GreatMarketrealmTabletop\Tabletop\Battle\Contracts\DeathSaveRoller;

defined('ABSPATH') || exit;

final class SecureDeathSaveRoller implements DeathSaveRoller
{
    public function roll(): int
    {
        return random_int(1, 20);
    }
}
