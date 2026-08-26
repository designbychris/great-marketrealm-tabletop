<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Encounters\Repositories;

use DateTimeImmutable;
use GreatMarketrealmTabletop\Tabletop\Encounters\Models\Encounter;
use GreatMarketrealmTabletop\Tabletop\Encounters\Repositories\WordPressEncounterRepository;
use PHPUnit\Framework\TestCase;

final class WordPressEncounterRepositoryTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['gmrt_test_options'] = [];
    }

    public function testEncountersPersistByTableAndSceneWithoutAutoload(): void
    {
        $repository = new WordPressEncounterRepository();
        $encounter = Encounter::prepare(
            'encounter-1',
            'table-1',
            'scene-1',
            'Pantry Ambush',
            new DateTimeImmutable()
        );

        $repository->save($encounter);

        self::assertSame(
            $encounter->toArray(),
            $repository->find('table-1', 'encounter-1')?->toArray()
        );
        self::assertCount(
            1,
            $repository->forScene('table-1', 'scene-1')
        );
        self::assertFalse(
            $GLOBALS['gmrt_test_options']['gmrt_table_encounters']['autoload']
        );
    }

    public function testEndedEncounterIsNotCurrent(): void
    {
        $repository = new WordPressEncounterRepository();
        $encounter = Encounter::prepare(
            'encounter-1',
            'table-1',
            'scene-1',
            'Done',
            new DateTimeImmutable()
        );
        $encounter->end();
        $repository->save($encounter);

        self::assertNull(
            $repository->currentForScene('table-1', 'scene-1')
        );
    }
}
