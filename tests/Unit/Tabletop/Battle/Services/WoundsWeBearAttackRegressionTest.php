<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Battle\Services;

use PHPUnit\Framework\TestCase;

final class WoundsWeBearAttackRegressionTest extends TestCase
{
    private string $source;

    protected function setUp(): void
    {
        $this->source = (string) file_get_contents(
            dirname(__DIR__, 5)
                . '/app/Tabletop/Battle/Services/AttackManager.php'
        );
    }

    public function testDefensesResolveBeforeVitalityDamage(): void
    {
        $resolve = strpos(
            $this->source,
            '$this->defenseResolver->resolve('
        );
        $damage = strpos(
            $this->source,
            '$vitality->damage('
        );

        self::assertIsInt($resolve);
        self::assertIsInt($damage);
        self::assertLessThan($damage, $resolve);
    }

    public function testDamageEventRecordsAdjustment(): void
    {
        self::assertStringContainsString(
            "'damage_adjustment' =>",
            $this->source
        );
    }
}
