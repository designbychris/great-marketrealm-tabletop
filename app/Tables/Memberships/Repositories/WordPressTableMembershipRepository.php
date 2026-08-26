<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tables\Memberships\Repositories;

use GreatMarketrealmTabletop\Tables\Memberships\Contracts\TableMembershipRepository;
use GreatMarketrealmTabletop\Tables\Memberships\Models\TableMember;

defined('ABSPATH') || exit;

final class WordPressTableMembershipRepository implements TableMembershipRepository
{
    private const OPTION = 'gmrt_table_memberships';

    /** @return array<int,TableMember> */
    public function forTable(string $tableId): array
    {
        $members = [];

        foreach (
            $this->records()[$tableId] ?? []
            as $record
        ) {
            if (is_array($record)) {
                $members[] = TableMember::reconstitute(
                    $record
                );
            }
        }

        return $members;
    }

    public function find(
        string $tableId,
        int $userId
    ): ?TableMember {
        $record = $this->records()[$tableId][(string) $userId]
            ?? null;

        return is_array($record)
            ? TableMember::reconstitute($record)
            : null;
    }

    public function save(TableMember $member): void
    {
        $records = $this->records();
        $records[$member->tableId()][
            (string) $member->userId()
        ] = $member->toArray();

        update_option(
            self::OPTION,
            $records,
            false
        );
    }

    /** @return array<string,array<string,array<string,mixed>>> */
    private function records(): array
    {
        $records = get_option(self::OPTION, []);

        return is_array($records)
            ? $records
            : [];
    }
}
