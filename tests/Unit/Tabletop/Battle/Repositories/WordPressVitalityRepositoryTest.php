<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Battle\Repositories;

use GreatMarketrealmTabletop\Tabletop\Battle\Models\Vitality;
use GreatMarketrealmTabletop\Tabletop\Battle\Repositories\WordPressVitalityRepository;
use PHPUnit\Framework\TestCase;

final class WordPressVitalityRepositoryTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['gmrt_test_options'] = [];
    }

    public function testMissingVitalityFallsBackToTenHp(): void
    {
        $vitality = (new WordPressVitalityRepository())
            ->forToken('table-1', 'token-a');

        self::assertSame(10, $vitality->maximumHp());
        self::assertSame(10, $vitality->currentHp());
        self::assertSame(0, $vitality->temporaryHp());
    }

    public function testSavedVitalityRoundTripsWithoutAutoload(): void
    {
        $repository = new WordPressVitalityRepository();
        $repository->save(
            'table-1',
            new Vitality('token-a', 24, 17, 2)
        );

        $vitality = $repository->forToken(
            'table-1',
            'token-a'
        );

        self::assertSame(24, $vitality->maximumHp());
        self::assertSame(17, $vitality->currentHp());
        self::assertFalse(
            $GLOBALS['gmrt_test_options']['gmrt_token_vitality']['autoload']
        );
    }
}
