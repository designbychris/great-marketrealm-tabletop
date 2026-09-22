<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class IndependentIllustratedWallCoverageStructuralComponentAuditRegressionTest extends TestCase
{
    public function test_g5z32_surveys_whole_mesh_without_admitting_ink_as_walls(): void
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertIsString($source);
        self::assertStringContainsString('const independentIllustratedWallSurvey = (() => {', $source);
        self::assertStringContainsString('for (let y = 2; y < contourRows - 2; y += 1)', $source);
        self::assertStringContainsString('candidateKeys.add(key)', $source);
        self::assertStringContainsString('pointSegmentDistance(gx, gy, a, b)', $source);
        self::assertStringContainsString("'unrepresented-ink-component-review'", $source);
        self::assertStringContainsString('wholeIllustrationCoverageCertified: false, missingWallsCertified: false,', $source);
        self::assertStringContainsString('admittedEdges: 0, restoredRuns: 0', $source);
        self::assertStringContainsString('                    independentIllustratedWallSurvey,', $source);
        self::assertStringContainsString('G.5Z.32 independent ink survey ·', $source);
    }
}
