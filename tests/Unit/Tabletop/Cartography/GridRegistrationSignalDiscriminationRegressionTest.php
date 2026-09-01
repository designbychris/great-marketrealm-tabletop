<?php
declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class GridRegistrationSignalDiscriminationRegressionTest extends TestCase
{
    public function testRegistrationExplicitlyDistinguishesPrintedGridFromHeavyWalls(): void
    {
        $js = (string) file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertStringContainsString('The Surveyor Learns the Difference Between a Grid and a Wall', $js);
        self::assertStringContainsString('Heavy architectural ink is deliberately negative evidence here', $js);
        self::assertStringContainsString('flankLight < 205 || center < 120', $js);
    }

    public function testThinLineRecoveryRewardsPaleGridStrokesInsideQuietFloor(): void
    {
        $js = (string) file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertStringContainsString('const recovery = Math.max(0, nearLight - center)', $js);
        self::assertStringContainsString('const thinness = .62 + Math.min(.78, recovery / 18)', $js);
        self::assertStringContainsString('const paleBias = .55 + (Math.min(255, center) / 255) * .45', $js);
    }

    public function testSparseRoomCadencesCannotWinAsPrintedGridEvidence(): void
    {
        $js = (string) file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertStringContainsString('Math.min(x.count, y.count) < 8', $js);
        self::assertStringContainsString('fewer than eight crossings is not sufficient', $js);
        self::assertStringContainsString('Math.min(best.x.coverage, best.y.coverage) < .28', $js);
    }

    public function testSupportedRoomSizeHarmonicsCanResolveToFundamentalGrid(): void
    {
        $js = (string) file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertStringContainsString('const fundamentalCandidate = (candidate) =>', $js);
        self::assertStringContainsString('for (let divisor = 6; divisor >= 2; divisor -= 1)', $js);
        self::assertStringContainsString('candidate.x.score * .42', $js);
        self::assertStringContainsString('candidate.y.score * .42', $js);
        self::assertStringContainsString('repeatsEnough', $js);
    }

    public function testCorrectiveIsDocumentedPreviewOnlyAndVersioned(): void
    {
        $roadmap = (string) file_get_contents(dirname(__DIR__, 4) . '/ROADMAP.md');
        $phase = (string) file_get_contents(dirname(__DIR__, 4) . '/docs/Roadmap/PHASE-IV.30.1F.1.md');
        $plugin = (string) file_get_contents(dirname(__DIR__, 4) . '/great-marketrealm-tabletop.php');
        self::assertStringContainsString('IV.30.1F.1 — The Surveyor Learns the Difference Between a Grid and a Wall', $roadmap);
        self::assertStringContainsString('faint small-square printed grid', $phase);
        self::assertStringContainsString('preview-only', $phase);
        self::assertStringContainsString('0.30.1-alpha.13', $plugin);
    }
}
