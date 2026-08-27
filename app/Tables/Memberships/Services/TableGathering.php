<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tables\Memberships\Services;

use GreatMarketrealmTabletop\Tables\Contracts\TableClock;
use GreatMarketrealmTabletop\Tables\Contracts\TableRepository;
use GreatMarketrealmTabletop\Tables\Memberships\Contracts\TableMembershipRepository;
use GreatMarketrealmTabletop\Tables\Memberships\Exceptions\TableMembershipException;
use GreatMarketrealmTabletop\Tables\Memberships\Models\TableMember;
use GreatMarketrealmTabletop\Tables\Memberships\Models\TableMemberStatus;
use GreatMarketrealmTabletop\Tables\Models\Table;
use GreatMarketrealmTabletop\Tables\Models\TableStatus;
use RuntimeException;

defined('ABSPATH') || exit;

final class TableGathering
{
    public function __construct(
        private TableRepository $tables,
        private TableMembershipRepository $members,
        private TableClock $clock
    ) {}

    public function seatDungeonMaster(
        Table $table
    ): TableMember {
        $existing = $this->members->find(
            $table->id(),
            $table->dungeonMasterUserId()
        );

        if ($existing !== null) {
            return $existing;
        }

        $member = TableMember::dungeonMaster(
            $table->id(),
            $table->dungeonMasterUserId(),
            $this->clock->now()
        );

        $this->members->save($member);

        return $member;
    }

    public function invitePlayer(
        string $tableId,
        int $userId
    ): TableMember {
        $table = $this->openTable($tableId);

        if ($userId === $table->dungeonMasterUserId()) {
            throw new TableMembershipException(
                'The Dungeon Master is already seated at this Table.'
            );
        }

        $existing = $this->members->find(
            $tableId,
            $userId
        );

        if (
            $existing !== null
            && $existing->status()
                !== TableMemberStatus::LEFT
        ) {
            return $existing;
        }

        $member = TableMember::invitePlayer(
            $tableId,
            $userId,
            $this->clock->now()
        );

        $this->members->save($member);

        return $member;
    }

    public function join(
        string $tableId,
        int $userId
    ): TableMember {
        $this->openTable($tableId);

        $member = $this->requiredMember(
            $tableId,
            $userId
        );

        $member->join($this->clock->now());
        $this->members->save($member);

        return $member;
    }

    public function leave(
        string $tableId,
        int $userId
    ): TableMember {
        $member = $this->requiredMember(
            $tableId,
            $userId
        );

        $member->leave($this->clock->now());
        $this->members->save($member);

        return $member;
    }

    public function removePlayer(string $tableId, int $userId): TableMember
    {
        $this->openTable($tableId);
        $member = $this->requiredMember($tableId, $userId);
        $member->removeByDungeonMaster($this->clock->now());
        $this->members->save($member);

        return $member;
    }

    public function selectCompanionCharacter(
        string $tableId,
        int $userId,
        string $characterId
    ): TableMember {
        $this->openTable($tableId);

        $member = $this->requiredMember(
            $tableId,
            $userId
        );

        $member->selectCompanionCharacter(
            $characterId
        );
        $this->members->save($member);

        return $member;
    }

    /** @return array<int,TableMember> */
    public function members(
        string $tableId
    ): array {
        $this->requiredTable($tableId);

        return $this->members->forTable($tableId);
    }

    private function openTable(
        string $tableId
    ): Table {
        $table = $this->requiredTable($tableId);

        if ($table->status() === TableStatus::ENDED) {
            throw new TableMembershipException(
                'No new gathering changes may be made to an ended Table.'
            );
        }

        return $table;
    }

    private function requiredTable(
        string $tableId
    ): Table {
        $table = $this->tables->find($tableId);

        if ($table === null) {
            throw new RuntimeException(
                'The requested Table could not be found.'
            );
        }

        return $table;
    }

    private function requiredMember(
        string $tableId,
        int $userId
    ): TableMember {
        $member = $this->members->find(
            $tableId,
            $userId
        );

        if ($member === null) {
            throw new TableMembershipException(
                'The user has not been invited to this Table.'
            );
        }

        return $member;
    }
}
