<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Battle\Repositories;

use GreatMarketrealmTabletop\Tabletop\Battle\Models\DamageDefenseProfile;
use GreatMarketrealmTabletop\Tabletop\Battle\Repositories\WordPressDamageDefenseRepository;
use PHPUnit\Framework\TestCase;

final class WordPressDamageDefenseRepositoryTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['gmrt_test_options'] = [];
    }

    public function testMissingDefenseProfileIsNeutral(): void
    {
        $profile = (new WordPressDamageDefenseRepository())
            ->forToken('table-1', 'token-a');

        self::assertFalse($profile->resists('fire'));
        self::assertFalse($profile->immuneTo('poison'));
    }

    public function testDefenseProfilePersistsWithoutAutoload(): void
    {
        $repository = new WordPressDamageDefenseRepository();
        $repository->save(
            'table-1',
            new DamageDefenseProfile(
                'token-a',
                ['slashing'],
                ['fire'],
                ['poison']
            )
        );

        $profile = $repository->forToken(
            'table-1',
            'token-a'
        );

        self::assertTrue($profile->resists('slashing'));
        self::assertTrue($profile->vulnerableTo('fire'));
        self::assertTrue($profile->immuneTo('poison'));
        self::assertFalse(
            $GLOBALS['gmrt_test_options']['gmrt_damage_defenses']['autoload']
        );
    }
}
