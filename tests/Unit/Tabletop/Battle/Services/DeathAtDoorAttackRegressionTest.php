<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Battle\Services;

use PHPUnit\Framework\TestCase;

final class DeathAtDoorAttackRegressionTest extends TestCase
{
    private string $source;

    protected function setUp(): void
    {
        $this->source = (string) file_get_contents(
            dirname(__DIR__, 5)
                . '/app/Tabletop/Battle/Services/AttackManager.php'
        );
    }

    public function testDamageAtZeroAppliesDeathSaveFailure(): void
    {
        self::assertStringContainsString(
            '$hpBefore === 0',
            $this->source
        );
        self::assertStringContainsString(
            'recordDamageFailure(',
            $this->source
        );
    }

    public function testCriticalHitAtZeroAppliesTwoFailures(): void
    {
        self::assertStringContainsString(
            'AttackOutcome::CRITICAL_HIT',
            $this->source
        );
        self::assertStringContainsString(
            '? 2',
            $this->source
        );
    }

    public function testMassiveDamageUsesExcessAgainstMaximumHp(): void
    {
        self::assertStringContainsString(
            "['excess_damage']",
            $this->source
        );
        self::assertStringContainsString(
            '>= $vitality->maximumHp()',
            $this->source
        );
        self::assertStringContainsString(
            'markFallen()',
            $this->source
        );
    }

    public function testDamageEventCarriesDeathConsequence(): void
    {
        self::assertStringContainsString(
            "'death_consequence' =>",
            $this->source
        );
        self::assertStringContainsString(
            "'death_saves' =>",
            $this->source
        );
    }
}
