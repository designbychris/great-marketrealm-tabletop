<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Battle\Services;

use GreatMarketrealmTabletop\Tabletop\Battle\Contracts\DeathSaveRepository;
use GreatMarketrealmTabletop\Tabletop\Battle\Contracts\VitalityRepository;
use GreatMarketrealmTabletop\Tabletop\Battle\Models\DeathSaveState;
use GreatMarketrealmTabletop\Tabletop\Battle\Models\Vitality;
use GreatMarketrealmTabletop\Tabletop\Battle\Services\VitalityRecoveryManager;
use PHPUnit\Framework\TestCase;

final class VitalityRecoveryManagerTest extends TestCase
{
    public function testHealingAboveZeroClearsDeathSaves(): void
    {
        $vitalityRepository = new class implements VitalityRepository {
            public Vitality $vitality;

            public function __construct()
            {
                $this->vitality = new Vitality(
                    'token-a',
                    20,
                    0
                );
            }

            public function forToken(
                string $tableId,
                string $tokenId
            ): Vitality {
                return $this->vitality;
            }

            public function save(
                string $tableId,
                Vitality $vitality
            ): void {
                $this->vitality = $vitality;
            }
        };

        $deathRepository = new class implements DeathSaveRepository {
            public DeathSaveState $state;

            public function __construct()
            {
                $this->state = new DeathSaveState(
                    'token-a',
                    1,
                    2
                );
            }

            public function forToken(
                string $tableId,
                string $tokenId
            ): DeathSaveState {
                return $this->state;
            }

            public function save(
                string $tableId,
                DeathSaveState $state
            ): void {
                $this->state = $state;
            }
        };

        $result = (new VitalityRecoveryManager(
            $vitalityRepository,
            $deathRepository
        ))->heal(
            'table-1',
            'token-a',
            7
        );

        self::assertSame(7, $result['vitality']->currentHp());
        self::assertSame(0, $result['death_saves']->successes());
        self::assertSame(0, $result['death_saves']->failures());
    }

    public function testZeroHealingDoesNotRecoverDownedCombatant(): void
    {
        $vitalityRepository = new class implements VitalityRepository {
            public function forToken(
                string $tableId,
                string $tokenId
            ): Vitality {
                return new Vitality($tokenId, 20, 0);
            }

            public function save(
                string $tableId,
                Vitality $vitality
            ): void {}
        };

        $deathRepository = new class implements DeathSaveRepository {
            public DeathSaveState $state;

            public function __construct()
            {
                $this->state = new DeathSaveState(
                    'token-a',
                    1,
                    1
                );
            }

            public function forToken(
                string $tableId,
                string $tokenId
            ): DeathSaveState {
                return $this->state;
            }

            public function save(
                string $tableId,
                DeathSaveState $state
            ): void {
                $this->state = $state;
            }
        };

        $result = (new VitalityRecoveryManager(
            $vitalityRepository,
            $deathRepository
        ))->heal(
            'table-1',
            'token-a',
            0
        );

        self::assertSame(0, $result['vitality']->currentHp());
        self::assertSame(1, $result['death_saves']->successes());
        self::assertSame(1, $result['death_saves']->failures());
    }
}
