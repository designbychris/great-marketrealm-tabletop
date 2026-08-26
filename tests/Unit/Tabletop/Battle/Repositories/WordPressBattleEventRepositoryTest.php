<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Battle\Repositories;

use DateTimeImmutable;
use GreatMarketrealmTabletop\Tabletop\Battle\Models\BattleEvent;
use GreatMarketrealmTabletop\Tabletop\Battle\Repositories\WordPressBattleEventRepository;
use PHPUnit\Framework\TestCase;

final class WordPressBattleEventRepositoryTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['gmrt_test_options'] = [];
    }

    public function testBattleEventsAppendInEncounterOrderWithoutAutoload(): void
    {
        $repository = new WordPressBattleEventRepository();

        foreach (['attack', 'help'] as $index => $deed) {
            $repository->append(
                new BattleEvent(
                    'event-' . $index,
                    'table-1',
                    'encounter-1',
                    'deed-performed',
                    'token-a',
                    1,
                    0,
                    new DateTimeImmutable(),
                    ['deed' => $deed]
                )
            );
        }

        $events = $repository->forEncounter(
            'table-1',
            'encounter-1'
        );

        self::assertCount(2, $events);
        self::assertSame(
            'attack',
            $events[0]->toArray()['payload']['deed']
        );
        self::assertFalse(
            $GLOBALS['gmrt_test_options']['gmrt_battle_events']['autoload']
        );
    }
}
