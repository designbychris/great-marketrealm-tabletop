<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Presentation;

use GreatMarketrealmTabletop\Tabletop\Presentation\CombatantStateProjector;
use PHPUnit\Framework\TestCase;

final class CombatantStateProjectorTest extends TestCase
{
    private CombatantStateProjector $projector;

    protected function setUp(): void
    {
        $this->projector = new CombatantStateProjector();
    }

    public function testFullHpIsHealthy(): void
    {
        self::assertSame(
            'healthy',
            $this->projector->project(
                ['type' => 'character'],
                ['current_hp' => 12, 'maximum_hp' => 12],
                ['dead' => false]
            )
        );
    }

    public function testPositiveDamagedHpIsWounded(): void
    {
        self::assertSame(
            'wounded',
            $this->projector->project(
                ['type' => 'character'],
                ['current_hp' => 7, 'maximum_hp' => 12],
                ['dead' => false]
            )
        );
    }

    public function testCharacterAtZeroHpIsDownedNotDead(): void
    {
        self::assertSame(
            'downed',
            $this->projector->project(
                ['type' => 'character'],
                ['current_hp' => 0, 'maximum_hp' => 12],
                ['dead' => false, 'stable' => false]
            )
        );
    }

    public function testCreatureAtZeroHpIsDefeated(): void
    {
        self::assertSame(
            'defeated',
            $this->projector->project(
                ['type' => 'creature'],
                ['current_hp' => 0, 'maximum_hp' => 18],
                ['dead' => false]
            )
        );
    }

    public function testConfirmedDeathIsDeceasedRegardlessOfTokenType(): void
    {
        self::assertSame(
            'deceased',
            $this->projector->project(
                ['type' => 'character'],
                ['current_hp' => 0, 'maximum_hp' => 12],
                ['dead' => true]
            )
        );
        self::assertSame(
            'deceased',
            $this->projector->project(
                ['type' => 'creature'],
                ['current_hp' => 0, 'maximum_hp' => 18],
                ['dead' => true]
            )
        );
    }

    public function testBadgesDistinguishDownKoAndDeath(): void
    {
        self::assertSame(
            'DOWN',
            $this->projector->badge('downed')
        );
        self::assertSame(
            'KO',
            $this->projector->badge('defeated')
        );
        self::assertSame(
            'DEAD',
            $this->projector->badge('deceased')
        );
        self::assertSame(
            '',
            $this->projector->badge('wounded')
        );
    }
}
