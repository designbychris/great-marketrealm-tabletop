<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Battle\Repositories;

use GreatMarketrealmTabletop\Tabletop\Battle\Models\DamageProfile;
use GreatMarketrealmTabletop\Tabletop\Battle\Repositories\WordPressDamageProfileRepository;
use PHPUnit\Framework\TestCase;

final class WordPressDamageProfileRepositoryTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['gmrt_test_options'] = [];
    }

    public function testDamageProfilePersistsWithoutAutoload(): void
    {
        $repository = new WordPressDamageProfileRepository();

        $repository->save(
            'table-1',
            new DamageProfile('token-a', 2, 8, 5)
        );

        $profile = $repository->forToken(
            'table-1',
            'token-a'
        );

        self::assertSame(2, $profile->diceCount());
        self::assertSame(8, $profile->dieSides());
        self::assertSame(5, $profile->modifier());
        self::assertFalse(
            $GLOBALS['gmrt_test_options']['gmrt_damage_profiles']['autoload']
        );
    }
}
