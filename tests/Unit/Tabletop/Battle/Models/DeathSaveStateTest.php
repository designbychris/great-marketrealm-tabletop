<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Battle\Models;

use GreatMarketrealmTabletop\Tabletop\Battle\Models\DeathSaveState;
use PHPUnit\Framework\TestCase;

final class DeathSaveStateTest extends TestCase
{
    public function testThreeSuccessesStabilizeCombatant(): void
    {
        $state = new DeathSaveState('token-a');
        $state->recordSuccess();
        $state->recordSuccess();
        $state->recordSuccess();

        self::assertTrue($state->stable());
        self::assertTrue($state->resolved());
    }

    public function testThreeFailuresMarkCombatantDead(): void
    {
        $state = new DeathSaveState('token-a');
        $state->recordFailure(2);
        $state->recordFailure();

        self::assertTrue($state->dead());
        self::assertTrue($state->resolved());
    }

    public function testResolvedSequenceIgnoresFurtherResults(): void
    {
        $state = new DeathSaveState('token-a', 3, 0, true);
        $state->recordFailure(2);

        self::assertSame(0, $state->failures());
    }

    public function testResetClearsSequence(): void
    {
        $state = new DeathSaveState('token-a', 2, 2);
        $state->reset();

        self::assertSame(0, $state->successes());
        self::assertSame(0, $state->failures());
        self::assertFalse($state->stable());
    }
}
