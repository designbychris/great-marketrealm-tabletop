<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tables\Contracts;

defined('ABSPATH') || exit;

interface TableIdGenerator
{
    public function generate(): string;
}
