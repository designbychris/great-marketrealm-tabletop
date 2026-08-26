<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Battle\Repositories;

use GreatMarketrealmTabletop\Tabletop\Battle\Models\DeathSaveState;
use GreatMarketrealmTabletop\Tabletop\Battle\Repositories\WordPressDeathSaveRepository;
use PHPUnit\Framework\TestCase;

final class WordPressDeathSaveRepositoryTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['gmrt_test_options'] = [];
    }

    public function testMissingStateStartsAtZero(): void
    {
        $state = (new WordPressDeathSaveRepository())
            ->forToken('table-1', 'token-a');

        self::assertSame(0, $state->successes());
        self::assertSame(0, $state->failures());
    }

    public function testStatePersistsWithoutAutoload(): void
    {
        $repository = new WordPressDeathSaveRepository();
        $state = new DeathSaveState('token-a');
        $state->recordFailure(2);
        $repository->save('table-1', $state);

        self::assertSame(
            2,
            $repository->forToken('table-1', 'token-a')->failures()
        );
        self::assertFalse(
            $GLOBALS['gmrt_test_options']['gmrt_death_saves']['autoload']
        );
    }
}
