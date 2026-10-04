<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class WallHasEndsRegressionTest extends TestCase
{
    public function test_g5z50r_retains_the_deterministic_bounded_bypass_and_classifies_where_it_escapes_the_fixed_route(): void
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertIsString($source);
        self::assertStringContainsString('IV.30.1G.5Z.50R — The Wall Has Ends.', $source);
        self::assertStringContainsString('const predecessors=new Map();', $source);
        self::assertStringContainsString('const barrierBypassEndAudit=', $source);
        self::assertStringContainsString('bypass-around-route-tangent-end-review', $source);
        self::assertStringContainsString('bypass-around-route-normal-edge-review', $source);
        self::assertStringContainsString('bypass-crosses-other-ink-band-review', $source);
        self::assertStringContainsString('contiguous-floor-bypass-review', $source);
        self::assertStringContainsString('G.5Z.50R wall has ends', $source);
        self::assertStringContainsString('deterministic-shortest-blocked-corridor-bypass;exact-bfs-predecessor-chain;same-three-cell-local-envelope;route-end-versus-normal-edge-versus-other-ink-versus-floor;no-second-flood;no-global-flood;no-route-shift;no-nearest-wall-search;no-recovery-replay;G.5Z.50-veto-unchanged', $source);
        self::assertStringContainsString('const maximumReviewSuggestions = 200;', $source);
        self::assertStringContainsString('const reconstructedSurfaceReviewCeiling = maximumReviewSuggestions;', $source);
        self::assertStringContainsString('const maximumPathVertices = 256;', $source);
    }
}
