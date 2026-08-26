<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tables\Scenes\Contracts;

defined('ABSPATH') || exit;

interface TableSceneIdGenerator
{
    public function generate(): string;
}
