<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tables\Memberships\Services;

use GreatMarketrealmTabletop\Tables\Memberships\Models\TableMember;

defined('ABSPATH') || exit;

final class TableMembershipPermissions
{
    public function mayManageTable(
        ?TableMember $member
    ): bool {
        return $member !== null
            && $member->isActive()
            && $member->isDungeonMaster();
    }

    public function mayParticipate(
        ?TableMember $member
    ): bool {
        return $member !== null
            && $member->isActive();
    }
}
