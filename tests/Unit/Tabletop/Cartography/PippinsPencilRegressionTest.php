<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class PippinsPencilRegressionTest extends TestCase
{
    public function test_pencil_pairs_existing_endpoints_without_changing_geometry(): void
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertIsString($source);
        self::assertStringContainsString('IV.30.2C.2 — Pippin Offers His Pencil', $source);
        self::assertStringContainsString('const ends = residualTerminations.filter', $source);
        self::assertStringContainsString('endpointIds:[a.id,b.id]', $source);
        self::assertStringContainsString('wallCertified:false, keeperApproved:false', $source);
        self::assertStringContainsString('proposedGeometry:0, changedGeometry:0', $source);
        self::assertStringContainsString('pippinsPencil,', $source);
        self::assertStringContainsString('IV.30.2C.2 Pippin offers his pencil', $source);
    }
}
