<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tables\Memberships\Services;

require_once __DIR__ . '/MembershipTestDoubles.php';
require_once dirname(__DIR__, 2)
    . '/Services/TableTestDoubles.php';

use DateTimeImmutable;
use GreatMarketrealmTabletop\Tables\Memberships\Exceptions\TableMembershipException;
use GreatMarketrealmTabletop\Tables\Memberships\Models\TableMemberStatus;
use GreatMarketrealmTabletop\Tables\Models\Table;
use GreatMarketrealmTabletop\Tests\Unit\Tables\Services\FixedClock;
use GreatMarketrealmTabletop\Tests\Unit\Tables\Services\InMemoryTableRepository;
use GreatMarketrealmTabletop\Tables\Memberships\Services\TableGathering;
use PHPUnit\Framework\TestCase;

final class TableGatheringTest extends TestCase
{
    private InMemoryTableRepository $tables;

    private InMemoryMembershipRepository $members;

    private FixedClock $clock;

    private TableGathering $gathering;

    protected function setUp(): void
    {
        $this->tables = new InMemoryTableRepository();
        $this->members = new InMemoryMembershipRepository();
        $this->clock = new FixedClock(
            new DateTimeImmutable(
                '2026-08-26T10:00:00+01:00'
            )
        );
        $this->gathering = new TableGathering(
            $this->tables,
            $this->members,
            $this->clock
        );

        $this->tables->save(
            Table::prepare(
                'table-1',
                42,
                'The Gathering',
                $this->clock->now()
            )
        );
    }

    public function testDungeonMasterCanBeSeatedOnce(): void
    {
        $table = $this->tables->find('table-1');

        self::assertNotNull($table);

        $first = $this->gathering
            ->seatDungeonMaster($table);
        $second = $this->gathering
            ->seatDungeonMaster($table);

        self::assertSame($first, $second);
        self::assertTrue($first->isDungeonMaster());
        self::assertCount(
            1,
            $this->gathering->members('table-1')
        );
    }

    public function testPlayerMustBeInvitedBeforeJoining(): void
    {
        $this->expectException(
            TableMembershipException::class
        );

        $this->gathering->join(
            'table-1',
            84
        );
    }

    public function testInvitedPlayerMayJoin(): void
    {
        $this->gathering->invitePlayer(
            'table-1',
            84
        );

        $member = $this->gathering->join(
            'table-1',
            84
        );

        self::assertSame(
            TableMemberStatus::ACTIVE,
            $member->status()
        );
    }

    public function testDungeonMasterCannotBeInvitedAsPlayer(): void
    {
        $this->expectException(
            TableMembershipException::class
        );

        $this->gathering->invitePlayer(
            'table-1',
            42
        );
    }

    public function testDuplicateInvitationIsIdempotent(): void
    {
        $first = $this->gathering->invitePlayer(
            'table-1',
            84
        );
        $second = $this->gathering->invitePlayer(
            'table-1',
            84
        );

        self::assertSame($first, $second);
        self::assertCount(
            1,
            $this->gathering->members('table-1')
        );
    }

    public function testPlayerMaySelectOpaqueCompanionCharacterReference(): void
    {
        $this->gathering->invitePlayer(
            'table-1',
            84
        );
        $this->gathering->join(
            'table-1',
            84
        );

        $member = $this->gathering
            ->selectCompanionCharacter(
                'table-1',
                84,
                'gmrc-character-27'
            );

        self::assertSame(
            'gmrc-character-27',
            $member->companionCharacterId()
        );
    }

    public function testEndedTableRejectsNewInvitations(): void
    {
        $table = $this->tables->find('table-1');
        self::assertNotNull($table);

        $now = $this->clock->now();
        $table->activate(
            $now,
            $now->modify('+15 minutes')
        );
        $table->end($now->modify('+1 hour'));
        $this->tables->save($table);

        $this->expectException(
            TableMembershipException::class
        );

        $this->gathering->invitePlayer(
            'table-1',
            84
        );
    }

    public function testLeftPlayerMayBeInvitedAgain(): void
    {
        $this->gathering->invitePlayer(
            'table-1',
            84
        );
        $this->gathering->join(
            'table-1',
            84
        );
        $this->gathering->leave(
            'table-1',
            84
        );

        $member = $this->gathering
            ->invitePlayer(
                'table-1',
                84
            );

        self::assertSame(
            TableMemberStatus::INVITED,
            $member->status()
        );
    }
}
