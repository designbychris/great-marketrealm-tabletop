<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Battle\Contracts;

defined('ABSPATH') || exit;

interface D20Roller
{
    public function roll(): int;
}
