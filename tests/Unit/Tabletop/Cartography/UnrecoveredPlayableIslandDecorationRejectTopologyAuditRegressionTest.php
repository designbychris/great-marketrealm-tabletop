<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class UnrecoveredPlayableIslandDecorationRejectTopologyAuditRegressionTest extends TestCase
{
    public function test_decoration_reject_topology_is_audited_without_readmitting_rejected_floor(): void
    {
        $script = file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertIsString($script);

        self::assertStringContainsString('IV.30.1G.5Z.4 — Unrecovered Playable-Island & Decoration-Reject Topology Audit.', $script);
        self::assertStringContainsString('illustratedDecorationRejectedFrontierCells', $script);
        self::assertStringContainsString('finalIllustratedDecorationRejectCells', $script);
        self::assertStringContainsString('illustratedDecorationRejectComponents', $script);
        self::assertStringContainsString('illustratedDecorationRejectAdjacentComponents', $script);
        self::assertStringContainsString('illustratedDecorationRejectMultiSidedComponents', $script);
        self::assertStringContainsString('illustratedDecorationRejectInteriorSupportedComponents', $script);
        self::assertStringContainsString('illustratedDecorationRejectIsolatedComponents', $script);
        self::assertStringContainsString('illustratedPotentialPlayableIslandComponents', $script);
        self::assertStringContainsString('illustratedPotentialPlayableIslandCells', $script);
        self::assertStringContainsString('unrecovered decoration cells', $script);
        self::assertStringContainsString('potential playable islands', $script);
        self::assertStringContainsString('G.5Y\'s decoration veto remains authoritative.', $script);
        self::assertStringNotContainsString('floor[row][column]=true; // G.5Z.4', $script);
    }
}
