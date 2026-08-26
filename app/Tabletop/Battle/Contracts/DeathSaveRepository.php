<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Battle\Contracts;

use GreatMarketrealmTabletop\Tabletop\Battle\Models\DeathSaveState;

defined('ABSPATH') || exit;

interface DeathSaveRepository
{
    public function forToken(
        string $tableId,
        string $tokenId
    ): DeathSaveState;

    public function save(
        string $tableId,
        DeathSaveState $state
    ): void;
}
