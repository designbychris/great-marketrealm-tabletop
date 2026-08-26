<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tables\Memberships\Services;

use GreatMarketrealmTabletop\Tables\Memberships\Contracts\TableMembershipRepository;
use GreatMarketrealmTabletop\Tables\Memberships\Models\TableMember;

final class InMemoryMembershipRepository implements TableMembershipRepository
{
    /** @var array<string,array<int,TableMember>> */
    private array $members = [];

    /** @return array<int,TableMember> */
    public function forTable(string $tableId): array
    {
        return array_values(
            $this->members[$tableId] ?? []
        );
    }

    public function find(
        string $tableId,
        int $userId
    ): ?TableMember {
        return $this->members[$tableId][$userId]
            ?? null;
    }

    public function save(TableMember $member): void
    {
        $this->members[$member->tableId()][
            $member->userId()
        ] = $member;
    }
}
