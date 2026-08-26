<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tables\Memberships\Models;

use GreatMarketrealmTabletop\Tables\Memberships\Models\TableMemberRole;
use GreatMarketrealmTabletop\Tables\Memberships\Models\TableMemberStatus;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class TableMemberVocabularyTest extends TestCase
{
    public function testRolesAreDungeonMasterAndPlayer(): void
    {
        self::assertSame(
            ['dungeon-master', 'player'],
            TableMemberRole::all()
        );
    }

    public function testStatusesAreInvitedActiveAndLeft(): void
    {
        self::assertSame(
            ['invited', 'active', 'left'],
            TableMemberStatus::all()
        );
    }

    public function testUnknownRoleIsRejected(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        TableMemberRole::assert('spectating-turnip');
    }

    public function testUnknownStatusIsRejected(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        TableMemberStatus::assert('lost-in-pantry');
    }
}
