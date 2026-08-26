<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Battle\Contracts;

use GreatMarketrealmTabletop\Tabletop\Battle\Models\DamageProfile;

defined('ABSPATH') || exit;

interface DamageProfileRepository
{
    public function forToken(
        string $tableId,
        string $tokenId
    ): DamageProfile;

    public function save(
        string $tableId,
        DamageProfile $profile
    ): void;
}
