<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class WholeRoomJudgementRegressionTest extends TestCase
{
    public function test_whole_room_review_groups_exact_existing_terminations_without_mutation(): void
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertIsString($source);
        self::assertStringContainsString('IV.30.2C.1 — The Cartographer Sees the Whole Room', $source);
        self::assertStringContainsString('for (const end of residualTerminations)', $source);
        self::assertStringContainsString('byPath.get(end.pathIndex).push(end)', $source);
        self::assertStringContainsString('componentIds, endpointIds:', $source);
        self::assertStringContainsString('diagnosticOnly: true, wallCertified: false', $source);
        self::assertStringContainsString('proposedGeometry: 0, changedGeometry: 0', $source);
        self::assertStringContainsString('wholeRoomJudgement,', $source);
        self::assertStringContainsString('IV.30.2C.1 whole-room structural review', $source);
        self::assertStringContainsString('no-wall-certification;no-geometry-mutation', $source);
    }
}
