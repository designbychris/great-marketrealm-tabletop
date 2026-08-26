<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Battle\Services;

use GreatMarketrealmTabletop\Tabletop\Battle\Contracts\DamageDieRoller;

defined('ABSPATH') || exit;

final class SecureDamageDieRoller implements DamageDieRoller
{
    public function roll(int $sides): int
    {
        return random_int(1, $sides);
    }
}
