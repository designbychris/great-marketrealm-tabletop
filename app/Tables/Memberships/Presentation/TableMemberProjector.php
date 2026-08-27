<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tables\Memberships\Presentation;

use GreatMarketrealmTabletop\Tables\Memberships\Contracts\TableMemberIdentityDirectory;
use GreatMarketrealmTabletop\Tables\Memberships\Models\TableMember;

defined('ABSPATH') || exit;

final class TableMemberProjector
{
    public function __construct(
        private TableMemberIdentityDirectory $identities
    ) {}

    /** @return array<string,mixed> */
    public function project(TableMember $member): array
    {
        return array_merge(
            $member->toArray(),
            $this->identities->forUser($member->userId())
        );
    }
}
