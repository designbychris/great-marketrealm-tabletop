<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Encounters\Services;

use GreatMarketrealmTabletop\Tables\Memberships\Models\TableMember;

defined('ABSPATH') || exit;

final class EncounterControlPolicy
{
    public function mayControl(?TableMember $member): bool
    {
        return $member !== null
            && $member->isActive()
            && $member->isDungeonMaster();
    }
}
