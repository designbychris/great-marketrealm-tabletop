<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Battle\Services;

use GreatMarketrealmTabletop\Tabletop\Battle\Contracts\D20Roller;

defined('ABSPATH') || exit;

final class SecureD20Roller implements D20Roller
{
    public function roll(): int
    {
        return random_int(1, 20);
    }
}
