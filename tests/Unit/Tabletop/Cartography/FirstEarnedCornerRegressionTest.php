<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class FirstEarnedCornerRegressionTest extends TestCase
{
    private function source(): string
    {
        return (string) file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
    }

    public function test_readiness_is_bounded_to_the_demonstrated_corner_and_original_veto_population(): void
    {
        $source = $this->source();
        self::assertStringContainsString('IV.30.1G.5Z.50Z — The First Earned Corner.', $source);
        self::assertStringContainsString("uniqueCells[0]==='140,63'", $source);
        self::assertStringContainsString('original.role===\'candidate-floor-open\'', $source);
        self::assertStringContainsString('replay.total===18&&replay.matched===18', $source);
        self::assertStringContainsString('closure?.beforeConnected===true&&closure?.afterConnected===false', $source);
    }

    public function test_readiness_is_published_without_changing_production_admission(): void
    {
        $source = $this->source();
        self::assertStringContainsString('illustratedPropagationBarrierFirstEarnedCornerReviews,', $source);
        self::assertStringContainsString('illustratedPropagationBarrierFirstEarnedCornerControlFailures,', $source);
        self::assertStringContainsString('first-earned-corner-production-readiness-review', $source);
    }
}
