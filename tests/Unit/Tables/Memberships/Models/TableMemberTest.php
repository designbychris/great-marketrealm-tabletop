<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tables\Memberships\Models;

use DateTimeImmutable;
use GreatMarketrealmTabletop\Tables\Memberships\Exceptions\TableMembershipException;
use GreatMarketrealmTabletop\Tables\Memberships\Models\TableMember;
use GreatMarketrealmTabletop\Tables\Memberships\Models\TableMemberRole;
use GreatMarketrealmTabletop\Tables\Memberships\Models\TableMemberStatus;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class TableMemberTest extends TestCase
{
    public function testDungeonMasterStartsActive(): void
    {
        $member = TableMember::dungeonMaster(
            'table-1',
            42,
            new DateTimeImmutable()
        );

        self::assertSame(
            TableMemberRole::DUNGEON_MASTER,
            $member->role()
        );
        self::assertSame(
            TableMemberStatus::ACTIVE,
            $member->status()
        );
        self::assertTrue($member->isDungeonMaster());
    }

    public function testPlayerStartsInvitedAndMayJoin(): void
    {
        $member = TableMember::invitePlayer(
            'table-1',
            84,
            new DateTimeImmutable()
        );

        self::assertSame(
            TableMemberStatus::INVITED,
            $member->status()
        );

        $member->join(new DateTimeImmutable());

        self::assertTrue($member->isActive());
    }

    public function testDungeonMasterCannotLeaveOwnTable(): void
    {
        $member = TableMember::dungeonMaster(
            'table-1',
            42,
            new DateTimeImmutable()
        );

        $this->expectException(
            TableMembershipException::class
        );

        $member->leave(new DateTimeImmutable());
    }

    public function testActivePlayerMayLeaveAndCharacterIsCleared(): void
    {
        $member = TableMember::invitePlayer(
            'table-1',
            84,
            new DateTimeImmutable()
        );
        $member->join(new DateTimeImmutable());
        $member->selectCompanionCharacter('character-12');
        $member->leave(new DateTimeImmutable());

        self::assertSame(
            TableMemberStatus::LEFT,
            $member->status()
        );
        self::assertNull(
            $member->companionCharacterId()
        );
    }

    public function testOnlyActiveMemberMaySelectCompanionCharacter(): void
    {
        $member = TableMember::invitePlayer(
            'table-1',
            84,
            new DateTimeImmutable()
        );

        $this->expectException(
            TableMembershipException::class
        );

        $member->selectCompanionCharacter(
            'character-12'
        );
    }

    public function testCompanionCharacterReferenceCannotBeEmpty(): void
    {
        $member = TableMember::dungeonMaster(
            'table-1',
            42,
            new DateTimeImmutable()
        );

        $this->expectException(
            InvalidArgumentException::class
        );

        $member->selectCompanionCharacter(' ');
    }

    public function testMembershipRoundTripsPersistentRecord(): void
    {
        $member = TableMember::invitePlayer(
            'table-1',
            84,
            new DateTimeImmutable(
                '2026-08-26T09:30:00+01:00'
            )
        );
        $member->join(
            new DateTimeImmutable(
                '2026-08-26T09:31:00+01:00'
            )
        );
        $member->selectCompanionCharacter(
            'character-12'
        );

        $restored = TableMember::reconstitute(
            $member->toArray()
        );

        self::assertSame(
            $member->toArray(),
            $restored->toArray()
        );
    }
}
