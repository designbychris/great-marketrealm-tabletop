<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tables\Tokens\Contracts;

defined('ABSPATH') || exit;

interface TableTokenIdGenerator
{
    public function generate(): string;
}
