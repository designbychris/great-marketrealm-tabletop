<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class CornerReceivesCertificateRegressionTest extends TestCase
{
    private function source(): string
    {
        return (string) file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
    }

    public function test_certificate_reconciles_only_the_legacy_spine_failure(): void
    {
        $source = $this->source();
        self::assertStringContainsString('IV.30.1G.5Z.50Z.1 — The Corner Receives Its Certificate.', $source);
        self::assertStringContainsString("original.failed.length===1&&original.failed[0]==='bounded-spine-review'", $source);
        self::assertStringContainsString('readiness.ready&&replay&&replay.qualified', $source);
        self::assertStringContainsString('readiness.demonstratedClosure&&readiness.inVetoPopulation', $source);
    }

    public function test_certificate_is_diagnostic_and_controls_remain_excluded(): void
    {
        $source = $this->source();
        self::assertStringContainsString("readiness.role!=='control-floor-both'", $source);
        self::assertStringContainsString('illustratedPropagationBarrierCornerCertificateControlFailures,', $source);
        self::assertStringContainsString('diagnostic-only;no-wall-admission;no-geometry-mutation;G.5Z.50V-gate-unchanged', $source);
    }
}
