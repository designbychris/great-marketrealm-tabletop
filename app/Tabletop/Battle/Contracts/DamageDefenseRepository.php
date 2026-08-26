<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Battle\Contracts;

use GreatMarketrealmTabletop\Tabletop\Battle\Models\DamageDefenseProfile;

defined('ABSPATH') || exit;

interface DamageDefenseRepository
{
    public function forToken(
        string $tableId,
        string $tokenId
    ): DamageDefenseProfile;

    public function save(
        string $tableId,
        DamageDefenseProfile $profile
    ): void;
}
