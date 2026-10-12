<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class CornerBorrowedInkRegressionTest extends TestCase
{
    public function test_borrowed_ink_is_bounded_and_never_certifies_a_wall(): void
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertIsString($source);
        self::assertStringContainsString("IV.30.1G.5Z.50Z.18 — The Corner's Borrowed Ink", $source);
        self::assertStringContainsString('illustratedPropagationBarrierCornerBorrowedInkReviews,', $source);
        self::assertStringContainsString('illustratedPropagationBarrierCornerBorrowedInkSamples,', $source);
        self::assertStringContainsString('neighbouring-ornament-favoured-review', $source);
        self::assertStringContainsString('isolated-directional-bridge-hypothesis-review', $source);
        self::assertStringContainsString('short-bracket-without-sustained-direction-review', $source);
        self::assertStringContainsString('if(gaps.has(key)){k++;continue;}', $source);
        self::assertStringContainsString("structuralPartner:'unproven',structuralHandshake:'unproven'", $source);
        self::assertStringContainsString('no-pixel-joining;no-snapping;no-wall-admission;no-recovery-replay', $source);
    }
}
