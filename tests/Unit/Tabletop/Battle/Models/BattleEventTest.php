<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Battle\Models;

use DateTimeImmutable;
use GreatMarketrealmTabletop\Tabletop\Battle\Models\BattleEvent;
use PHPUnit\Framework\TestCase;

final class BattleEventTest extends TestCase
{
    public function testBattleEventRoundTripsStructuredPayload(): void
    {
        $event = new BattleEvent(
            'event-1',
            'table-1',
            'encounter-1',
            'deed-performed',
            'token-a',
            2,
            1,
            new DateTimeImmutable('2026-08-26T10:00:00+01:00'),
            ['deed' => 'attack', 'resource' => 'action']
        );

        self::assertSame(
            $event->toArray(),
            BattleEvent::reconstitute($event->toArray())->toArray()
        );
    }
}
