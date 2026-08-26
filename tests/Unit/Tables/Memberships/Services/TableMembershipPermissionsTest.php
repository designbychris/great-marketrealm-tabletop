<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tables\Memberships\Services;

use DateTimeImmutable;
use GreatMarketrealmTabletop\Tables\Memberships\Models\TableMember;
use PHPUnit\Framework\TestCase;

final class TableMembershipPermissionsTest extends TestCase
{
    public function testActiveDungeonMasterMayManageAndParticipate(): void
    {
        $member = TableMember::dungeonMaster(
            'table-1',
            42,
            new DateTimeImmutable()
        );
        $permissions = new TableMembershipPermissions();

        self::assertTrue(
            $permissions->mayManageTable($member)
        );
        self::assertTrue(
            $permissions->mayParticipate($member)
        );
    }

    public function testActivePlayerMayParticipateButNotManage(): void
    {
        $member = TableMember::invitePlayer(
            'table-1',
            84,
            new DateTimeImmutable()
        );
        $member->join(new DateTimeImmutable());

        $permissions = new TableMembershipPermissions();

        self::assertFalse(
            $permissions->mayManageTable($member)
        );
        self::assertTrue(
            $permissions->mayParticipate($member)
        );
    }

    public function testInvitedPlayerCannotParticipateYet(): void
    {
        $member = TableMember::invitePlayer(
            'table-1',
            84,
            new DateTimeImmutable()
        );

        self::assertFalse(
            (new TableMembershipPermissions())
                ->mayParticipate($member)
        );
    }
}
