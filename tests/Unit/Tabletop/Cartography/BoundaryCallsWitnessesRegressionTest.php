<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class BoundaryCallsWitnessesRegressionTest extends TestCase
{
    public function test_witness_audit_is_bounded_and_diagnostic_only(): void
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertIsString($source);
        self::assertStringContainsString('IV.30.1G.5Z.50Z.3 — The Boundary Calls Its Witnesses.', $source);
        self::assertStringContainsString('review.corners.slice(0,1)', $source);
        self::assertStringContainsString('barrierCornerBoundaryWitnessReviews', $source);
        self::assertStringContainsString('illustratedPropagationBarrierCornerBoundaryWitnessInterfaces,', $source);
        self::assertStringContainsString('diagnostic-only;original-ink-directional-witnesses;no-wall-admission;no-recovery-replay;no-geometry-mutation', $source);
    }
}
