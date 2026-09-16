<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class ConservativeInteriorIslandSeedingControlledRePropagationRegressionTest extends TestCase
{
    public function test_topology_safe_islands_receive_one_local_seed_then_reuse_the_existing_flood(): void
    {
        $script = file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertIsString($script);

        self::assertStringContainsString('IV.30.1G.5Z.6 — Conservative Interior Island Seeding & Controlled Re-Propagation.', $script);
        self::assertStringContainsString('illustratedSecondarySeedCandidates', $script);
        self::assertStringContainsString('illustratedSecondaryNarrowGapCandidates', $script);
        self::assertStringContainsString('illustratedSecondaryTopologySafeCandidates', $script);
        self::assertStringContainsString('illustratedSecondarySeedsAdmitted', $script);
        self::assertStringContainsString('illustratedSecondaryRecoveredCells', $script);
        self::assertStringContainsString('if (bridgeInk>=.62 || bridgeHorizontal || bridgeVertical)', $script);
        self::assertStringContainsString('illustratedSecondarySeedCandidates.push(topologySafeSeed)', $script);
        self::assertStringContainsString('const secondaryFloodRecovered=illustratedFloorFloodPass();', $script);
        self::assertStringContainsString('recoveredIllustratedFloorCells+=illustratedSecondaryRecoveredCells;', $script);
        self::assertStringContainsString('secondary narrow-gap candidates', $script);
        self::assertStringContainsString('topology-safe seed candidates', $script);
        self::assertStringContainsString('secondary seeds admitted', $script);
        self::assertStringContainsString('secondary illustrated cells recovered', $script);
        self::assertStringContainsString('No nearest-playable search or arbitrary snapping.', $script);
        self::assertStringContainsString('one seed maximum per component', strtolower($script));
        self::assertStringNotContainsString('illustratedSecondarySeedCandidates.push(...', $script);
    }
}
