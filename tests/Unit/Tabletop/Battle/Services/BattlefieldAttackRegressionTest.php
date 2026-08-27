<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Battle\Services;

use PHPUnit\Framework\TestCase;

final class BattlefieldAttackRegressionTest extends TestCase
{
    private string $source;

    protected function setUp(): void
    {
        $this->source = (string) file_get_contents(
            dirname(__DIR__, 5)
                . '/app/Tabletop/Battle/Services/AttackManager.php'
        );
    }

    public function testRangeIsMeasuredBeforeAttackDeedIsSpent(): void
    {
        $measure = strpos(
            $this->source,
            '$this->battlefield->between('
        );
        $deed = strpos(
            $this->source,
            '$this->deeds->perform('
        );

        self::assertIsInt($measure);
        self::assertIsInt($deed);
        self::assertLessThan($deed, $measure);
    }

    public function testOutOfRangeAttackIsRejectedServerSide(): void
    {
        self::assertStringContainsString(
            'if (! $range->inRange())',
            $this->source
        );
        self::assertStringContainsString(
            'Out of range: target is %d ft away',
            $this->source
        );
    }

    public function testLongRangeContributesDisadvantage(): void
    {
        self::assertStringContainsString(
            '$range->longRange()',
            $this->source
        );
        self::assertStringContainsString(
            'AttackRollMode::fromFactors(',
            $this->source
        );
    }

    public function testAttackEventRecordsTargetingAssessment(): void
    {
        self::assertStringContainsString(
            "'targeting' => \$range?->toArray()",
            $this->source
        );
    }
}
