<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tables\Memberships\Contracts;

use GreatMarketrealmTabletop\Tables\Memberships\Models\TableMember;

defined('ABSPATH') || exit;

interface TableMembershipRepository
{
    /** @return array<int,TableMember> */
    public function forTable(string $tableId): array;

    public function find(
        string $tableId,
        int $userId
    ): ?TableMember;

    public function save(TableMember $member): void;
}
