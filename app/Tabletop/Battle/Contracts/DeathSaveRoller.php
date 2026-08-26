<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Battle\Contracts;

defined('ABSPATH') || exit;

interface DeathSaveRoller
{
    public function roll(): int;
}
