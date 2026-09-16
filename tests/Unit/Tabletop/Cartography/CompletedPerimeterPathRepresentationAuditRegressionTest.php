<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class CompletedPerimeterPathRepresentationAuditRegressionTest extends TestCase
{
    public function test_completed_surface_perimeter_publishes_path_representation_edge_accounting(): void
    {
        $script = file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertIsString($script);

        self::assertStringContainsString('IV.30.1G.5Z.2 — Completed Perimeter Path Representation Audit.', $script);
        self::assertStringContainsString('reconstructedSurfaceAssembledEdges', $script);
        self::assertStringContainsString('reconstructedSurfaceClosedChains', $script);
        self::assertStringContainsString('reconstructedSurfaceOpenChains', $script);
        self::assertStringContainsString('reconstructedSurfaceAlreadyRepresentedEdges', $script);
        self::assertStringContainsString('reconstructedSurfaceAuthorityExtensionEdges', $script);
        self::assertStringContainsString('reconstructedSurfaceNovelRepresentedEdges', $script);
        self::assertStringContainsString('reconstructedSurfaceRepresentedEdges:', $script);
        self::assertStringContainsString('reconstructedSurfaceUncoveredEdges:', $script);
        self::assertStringContainsString('uncovered surface edges', $script);
    }
}
