<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Conditions\Services;

use DateTimeImmutable;
use GreatMarketrealmTabletop\Tabletop\Battle\Models\AttackRollMode;
use GreatMarketrealmTabletop\Tabletop\Conditions\Models\TokenCondition;
use GreatMarketrealmTabletop\Tabletop\Conditions\Services\ConditionCombatRules;
use PHPUnit\Framework\TestCase;

final class ConditionCombatRulesTest extends TestCase
{
    private ConditionCombatRules $rules;

    protected function setUp(): void
    {
        $this->rules = new ConditionCombatRules();
    }

    public function testPoisonedAttackerHasDisadvantage(): void
    {
        self::assertSame(
            AttackRollMode::DISADVANTAGE,
            $this->rules->attackRollMode(
                [$this->condition('poisoned')],
                []
            )
        );
    }

    public function testBlindedAttackerHasDisadvantage(): void
    {
        self::assertSame(
            AttackRollMode::DISADVANTAGE,
            $this->rules->attackRollMode(
                [$this->condition('blinded')],
                []
            )
        );
    }

    public function testProneAttackerHasDisadvantage(): void
    {
        self::assertSame(
            AttackRollMode::DISADVANTAGE,
            $this->rules->attackRollMode(
                [$this->condition('prone')],
                []
            )
        );
    }

    public function testRestrainedAttackerHasDisadvantage(): void
    {
        self::assertSame(
            AttackRollMode::DISADVANTAGE,
            $this->rules->attackRollMode(
                [$this->condition('restrained')],
                []
            )
        );
    }

    public function testBlindedTargetGrantsAdvantage(): void
    {
        self::assertSame(
            AttackRollMode::ADVANTAGE,
            $this->rules->attackRollMode(
                [],
                [$this->condition('blinded')]
            )
        );
    }

    public function testRestrainedTargetGrantsAdvantage(): void
    {
        self::assertSame(
            AttackRollMode::ADVANTAGE,
            $this->rules->attackRollMode(
                [],
                [$this->condition('restrained')]
            )
        );
    }

    public function testStunnedTargetGrantsAdvantage(): void
    {
        self::assertSame(
            AttackRollMode::ADVANTAGE,
            $this->rules->attackRollMode(
                [],
                [$this->condition('stunned')]
            )
        );
    }

    public function testAdvantageAndDisadvantageCancel(): void
    {
        self::assertSame(
            AttackRollMode::NORMAL,
            $this->rules->attackRollMode(
                [$this->condition('poisoned')],
                [$this->condition('stunned')]
            )
        );
    }

    public function testStunnedBlocksBattleDeeds(): void
    {
        self::assertTrue(
            $this->rules->blocksBattleDeeds(
                [$this->condition('stunned')]
            )
        );
    }

    public function testGrappledRestrainedAndStunnedBlockMovement(): void
    {
        foreach (['grappled', 'restrained', 'stunned'] as $type) {
            self::assertTrue(
                $this->rules->blocksMovement(
                    [$this->condition($type)]
                )
            );
        }
    }

    public function testPoisonedDoesNotBlockMovement(): void
    {
        self::assertFalse(
            $this->rules->blocksMovement(
                [$this->condition('poisoned')]
            )
        );
    }

    private function condition(string $type): TokenCondition
    {
        return new TokenCondition(
            'token-a',
            $type,
            null,
            new DateTimeImmutable()
        );
    }
}
