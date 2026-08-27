<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tables\Memberships\Repositories;

use GreatMarketrealmTabletop\Tables\Memberships\Repositories\WordPressTableMemberIdentityDirectory;
use PHPUnit\Framework\TestCase;

final class WordPressTableMemberIdentityDirectoryTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['gmrt_test_users'] = [
            42 => [
                'ID' => 42,
                'display_name' => 'Chris',
                'user_login' => 'chris',
                'user_email' => 'chris@example.test',
            ],
            84 => [
                'ID' => 84,
                'display_name' => 'Mira',
                'user_login' => 'mira',
                'user_email' => 'mira@example.test',
            ],
        ];
    }

    public function testProjectsWordPressDisplayNameAndAvatar(): void
    {
        $identity = (new WordPressTableMemberIdentityDirectory())
            ->forUser(42);

        self::assertSame('Chris', $identity['display_name']);
        self::assertSame(
            'https://example.test/avatar/42.png',
            $identity['avatar_url']
        );
    }

    public function testResolvesExistingUserByEmail(): void
    {
        self::assertSame(
            84,
            (new WordPressTableMemberIdentityDirectory())
                ->resolve('mira@example.test')
        );
    }

    public function testResolvesExistingUserByLogin(): void
    {
        self::assertSame(
            84,
            (new WordPressTableMemberIdentityDirectory())
                ->resolve('mira')
        );
    }

    public function testResolvesExistingUserByNumericId(): void
    {
        self::assertSame(
            42,
            (new WordPressTableMemberIdentityDirectory())
                ->resolve('42')
        );
    }

    public function testUnknownUserIsNotResolved(): void
    {
        self::assertNull(
            (new WordPressTableMemberIdentityDirectory())
                ->resolve('nobody@example.test')
        );
    }
}
