<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Battle\Contracts;

use GreatMarketrealmTabletop\Tabletop\Battle\Models\Vitality;

defined('ABSPATH') || exit;

interface VitalityRepository
{
    public function forToken(
        string $tableId,
        string $tokenId
    ): Vitality;

    public function save(
        string $tableId,
        Vitality $vitality
    ): void;
}
