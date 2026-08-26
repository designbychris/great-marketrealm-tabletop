<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tables\Memberships\Repositories;

use DateTimeImmutable;
use GreatMarketrealmTabletop\Tables\Memberships\Models\TableMember;
use GreatMarketrealmTabletop\Tables\Memberships\Repositories\WordPressTableMembershipRepository;
use PHPUnit\Framework\TestCase;

final class WordPressTableMembershipRepositoryTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['gmrt_test_options'] = [];
    }

    public function testMembershipPersistsByTableAndUser(): void
    {
        $repository = new WordPressTableMembershipRepository();
        $member = TableMember::invitePlayer(
            'table-1',
            84,
            new DateTimeImmutable()
        );

        $repository->save($member);

        self::assertSame(
            $member->toArray(),
            $repository->find(
                'table-1',
                84
            )?->toArray()
        );
    }

    public function testTablesHaveIndependentMembershipLists(): void
    {
        $repository = new WordPressTableMembershipRepository();

        $repository->save(
            TableMember::invitePlayer(
                'table-1',
                84,
                new DateTimeImmutable()
            )
        );
        $repository->save(
            TableMember::invitePlayer(
                'table-2',
                126,
                new DateTimeImmutable()
            )
        );

        self::assertCount(
            1,
            $repository->forTable('table-1')
        );
        self::assertCount(
            1,
            $repository->forTable('table-2')
        );
    }
}
