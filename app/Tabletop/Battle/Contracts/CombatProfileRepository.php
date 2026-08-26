<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Battle\Contracts;

use GreatMarketrealmTabletop\Tabletop\Battle\Models\CombatProfile;

defined('ABSPATH') || exit;

interface CombatProfileRepository
{
    public function forToken(
        string $tableId,
        string $tokenId
    ): CombatProfile;

    public function save(
        string $tableId,
        CombatProfile $profile
    ): void;
}
