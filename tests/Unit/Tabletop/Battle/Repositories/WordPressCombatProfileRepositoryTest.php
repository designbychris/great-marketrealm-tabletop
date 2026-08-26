<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Battle\Repositories;

use GreatMarketrealmTabletop\Tabletop\Battle\Models\CombatProfile;
use GreatMarketrealmTabletop\Tabletop\Battle\Repositories\WordPressCombatProfileRepository;
use PHPUnit\Framework\TestCase;

final class WordPressCombatProfileRepositoryTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['gmrt_test_options'] = [];
    }

    public function testMissingProfileFallsBackToAcTenAndZeroAttackBonus(): void
    {
        $profile = (new WordPressCombatProfileRepository())
            ->forToken('table-1', 'token-a');

        self::assertSame(10, $profile->armorClass());
        self::assertSame(0, $profile->attackModifier());
    }

    public function testSavedProfileRoundTripsWithoutAutoload(): void
    {
        $repository = new WordPressCombatProfileRepository();
        $repository->save(
            'table-1',
            new CombatProfile('token-a', 18, 6)
        );

        $profile = $repository->forToken(
            'table-1',
            'token-a'
        );

        self::assertSame(18, $profile->armorClass());
        self::assertSame(6, $profile->attackModifier());
        self::assertFalse(
            $GLOBALS['gmrt_test_options']['gmrt_combat_profiles']['autoload']
        );
    }
}
