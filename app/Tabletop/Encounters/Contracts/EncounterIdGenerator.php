<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Encounters\Contracts;

defined('ABSPATH') || exit;

interface EncounterIdGenerator
{
    public function generate(): string;
}
