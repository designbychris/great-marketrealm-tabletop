<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Encounters\Models;

use DateTimeImmutable;
use GreatMarketrealmTabletop\Tabletop\Encounters\Exceptions\EncounterStateException;
use GreatMarketrealmTabletop\Tabletop\Encounters\Models\Encounter;
use GreatMarketrealmTabletop\Tabletop\Encounters\Models\EncounterCombatant;
use GreatMarketrealmTabletop\Tabletop\Encounters\Models\EncounterStatus;
use PHPUnit\Framework\TestCase;

final class EncounterTest extends TestCase
{
    public function testPreparingEncounterBeginsAtRevisionOne(): void
    {
        $encounter = $this->encounter();

        self::assertSame(EncounterStatus::PREPARING, $encounter->status());
        self::assertSame(0, $encounter->round());
        self::assertSame(1, $encounter->revision());
        self::assertNull($encounter->currentCombatant());
    }

    public function testStartSortsInitiativeDeterministically(): void
    {
        $encounter = $this->encounter();

        $encounter->addCombatant(new EncounterCombatant('token-c', 15, 2));
        $encounter->addCombatant(new EncounterCombatant('token-b', 15, 3));
        $encounter->addCombatant(new EncounterCombatant('token-a', 15, 3));
        $encounter->addCombatant(new EncounterCombatant('token-d', 9, 5));
        $encounter->start();

        self::assertSame(
            ['token-a', 'token-b', 'token-c', 'token-d'],
            array_map(
                static fn (EncounterCombatant $combatant): string =>
                    $combatant->tokenId(),
                $encounter->combatants()
            )
        );
        self::assertSame(1, $encounter->round());
        self::assertSame('token-a', $encounter->currentCombatant()?->tokenId());
    }

    public function testTurnWrapStartsNextRound(): void
    {
        $encounter = $this->encounter();
        $encounter->addCombatant(new EncounterCombatant('token-a', 20));
        $encounter->addCombatant(new EncounterCombatant('token-b', 10));
        $encounter->start();

        $encounter->advanceTurn();
        self::assertSame('token-b', $encounter->currentCombatant()?->tokenId());
        self::assertSame(1, $encounter->round());

        $encounter->advanceTurn();
        self::assertSame('token-a', $encounter->currentCombatant()?->tokenId());
        self::assertSame(2, $encounter->round());
    }

    public function testPauseAndResumePreserveTurn(): void
    {
        $encounter = $this->encounter();
        $encounter->addCombatant(new EncounterCombatant('token-a', 10));
        $encounter->start();
        $encounter->pause();

        self::assertSame(EncounterStatus::PAUSED, $encounter->status());
        self::assertSame('token-a', $encounter->currentCombatant()?->tokenId());

        $encounter->resume();
        self::assertSame(EncounterStatus::ACTIVE, $encounter->status());
    }

    public function testEncounterCannotStartWithoutCombatants(): void
    {
        $this->expectException(EncounterStateException::class);
        $this->encounter()->start();
    }

    public function testDuplicateTokenCannotJoinEncounterTwice(): void
    {
        $encounter = $this->encounter();
        $encounter->addCombatant(new EncounterCombatant('token-a', 10));

        $this->expectException(EncounterStateException::class);
        $encounter->addCombatant(new EncounterCombatant('token-a', 8));
    }

    public function testEncounterRoundTripsPersistentState(): void
    {
        $encounter = $this->encounter();
        $encounter->addCombatant(new EncounterCombatant('token-a', 20, 4));
        $encounter->start();
        $encounter->pause();

        $copy = Encounter::reconstitute($encounter->toArray());

        self::assertSame($encounter->toArray(), $copy->toArray());
    }

    private function encounter(): Encounter
    {
        return Encounter::prepare(
            'encounter-1',
            'table-1',
            'scene-1',
            'Pantry Ambush',
            new DateTimeImmutable('2026-08-26T10:00:00+01:00')
        );
    }

    public function testAdvancingTurnResetsTurnEconomy(): void
    {
        $encounter = $this->encounter();
        $encounter->addCombatant(new EncounterCombatant('token-a', 20));
        $encounter->addCombatant(new EncounterCombatant('token-b', 10));
        $encounter->start();

        $encounter->spendTurnResource(
            \GreatMarketrealmTabletop\Tabletop\Battle\Models\TurnResource::ACTION
        );

        self::assertTrue(
            $encounter->turnEconomy()->isSpent(
                \GreatMarketrealmTabletop\Tabletop\Battle\Models\TurnResource::ACTION
            )
        );

        $encounter->advanceTurn();

        self::assertFalse(
            $encounter->turnEconomy()->isSpent(
                \GreatMarketrealmTabletop\Tabletop\Battle\Models\TurnResource::ACTION
            )
        );
    }

}
