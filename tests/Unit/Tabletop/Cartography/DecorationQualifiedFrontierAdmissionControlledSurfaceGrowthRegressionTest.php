<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class DecorationQualifiedFrontierAdmissionControlledSurfaceGrowthRegressionTest extends TestCase
{
    public function test_decoration_qualified_frontier_can_take_a_contiguous_provisional_step_without_weakening_vetoes(): void
    {
        $root = dirname(__DIR__, 4);
        $script = (string) file_get_contents($root . '/assets/js/tabletop.js');
        $roadmap = (string) file_get_contents($root . '/ROADMAP.md');

        self::assertStringContainsString('IV.30.1G.5V — Decoration-Qualified Frontier Admission & Controlled Surface Growth', $script);
        self::assertStringContainsString('const provisionalDecorationAdmission = !localInteriorSupport && adjacentPlayable >= 1;', $script);
        self::assertStringContainsString('if (!inkIsPlausibleDecoration) { illustratedFrontierDecorationRejects+=1; continue; }', $script);
        self::assertStringContainsString('if (!localInteriorSupport && !provisionalDecorationAdmission) { illustratedFrontierInteriorSupportRejects+=1; continue; }', $script);
        self::assertStringContainsString('if (provisionalDecorationAdmission) illustratedFrontierProvisionalAdmissions+=1;', $script);
        self::assertStringContainsString('provisional illustrated admissions', $script);
        self::assertStringContainsString('horizontalStructuralBand || verticalStructuralBand', $script);
        self::assertStringContainsString('if (exteriorLike) { illustratedFrontierExteriorRejects+=1; continue; }', $script);
        self::assertStringContainsString('Phase IV.30.1G.5V — Decoration-Qualified Frontier Admission & Controlled Surface Growth ✅', $roadmap);
    }
}
