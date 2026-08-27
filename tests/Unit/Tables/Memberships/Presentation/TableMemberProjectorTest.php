<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tables\Memberships\Presentation;

use DateTimeImmutable;
use GreatMarketrealmTabletop\Tables\Memberships\Contracts\TableMemberIdentityDirectory;
use GreatMarketrealmTabletop\Tables\Memberships\Models\TableMember;
use GreatMarketrealmTabletop\Tables\Memberships\Presentation\TableMemberProjector;
use PHPUnit\Framework\TestCase;

final class TableMemberProjectorTest extends TestCase
{
    public function testProjectionKeepsMembershipAndAddsHumanIdentity(): void
    {
        $directory = new class implements TableMemberIdentityDirectory {
            public function forUser(int $userId): array
            {
                return [
                    'user_id' => $userId,
                    'display_name' => 'Keeper Chris',
                    'avatar_url' => 'avatar.png',
                ];
            }

            public function resolve(string $identifier): ?int
            {
                return null;
            }
        };

        $member = TableMember::dungeonMaster(
            'table-1',
            42,
            new DateTimeImmutable()
        );
        $projection = (new TableMemberProjector($directory))
            ->project($member);

        self::assertSame('Keeper Chris', $projection['display_name']);
        self::assertSame('avatar.png', $projection['avatar_url']);
        self::assertSame('dungeon-master', $projection['role']);
        self::assertSame('active', $projection['status']);
    }
}
