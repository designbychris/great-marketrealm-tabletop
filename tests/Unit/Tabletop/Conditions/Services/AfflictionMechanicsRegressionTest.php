<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Conditions\Services;

use PHPUnit\Framework\TestCase;

final class AfflictionMechanicsRegressionTest extends TestCase
{
    public function testAttackManagerUsesConditionRollMode(): void
    {
        $source = (string) file_get_contents(
            dirname(__DIR__, 5)
                . '/app/Tabletop/Battle/Services/AttackManager.php'
        );

        self::assertStringContainsString(
            '->attackRollMode(',
            $source
        );
        self::assertStringContainsString(
            '$this->conditions->forToken(',
            $source
        );
    }

    public function testStunnedCombatantIsBlockedFromBattleDeeds(): void
    {
        $source = (string) file_get_contents(
            dirname(__DIR__, 5)
                . '/app/Tabletop/Battle/Services/BattleDeedManager.php'
        );

        self::assertStringContainsString(
            'blocksBattleDeeds(',
            $source
        );
        self::assertStringContainsString(
            'A stunned combatant cannot perform battle deeds.',
            $source
        );
    }

    public function testMovementUsesConditionRules(): void
    {
        $source = (string) file_get_contents(
            dirname(__DIR__, 5)
                . '/app/Tabletop/Movement/Services/TabletopMovement.php'
        );

        self::assertStringContainsString(
            'blocksMovement(',
            $source
        );
        self::assertStringContainsString(
            'grappled, restrained, or stunned',
            $source
        );
    }
}
