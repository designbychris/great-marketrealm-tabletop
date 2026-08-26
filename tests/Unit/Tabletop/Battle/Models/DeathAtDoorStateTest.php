<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Battle\Models;

use GreatMarketrealmTabletop\Tabletop\Battle\Models\DeathSaveState;
use PHPUnit\Framework\TestCase;

final class DeathAtDoorStateTest extends TestCase
{
    public function testDamageAtZeroAddsOneFailure(): void
    {
        $state = new DeathSaveState('token-a');

        $state->recordDamageFailure();

        self::assertSame(1, $state->failures());
    }

    public function testCriticalDamageAtZeroMayAddTwoFailures(): void
    {
        $state = new DeathSaveState('token-a');

        $state->recordDamageFailure(2);

        self::assertSame(2, $state->failures());
    }

    public function testDamageBreaksStabilisation(): void
    {
        $state = new DeathSaveState(
            'token-a',
            3,
            0,
            true
        );

        $state->recordDamageFailure();

        self::assertFalse($state->stable());
        self::assertSame(1, $state->failures());
        self::assertFalse($state->resolved());
    }

    public function testMassiveDamageMarksCombatantFallen(): void
    {
        $state = new DeathSaveState('token-a');

        $state->markFallen();

        self::assertTrue($state->dead());
        self::assertSame(3, $state->failures());
    }
}
