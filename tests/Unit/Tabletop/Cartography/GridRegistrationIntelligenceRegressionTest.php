<?php
declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class GridRegistrationIntelligenceRegressionTest extends TestCase
{
    public function testKeeperCanAskPippinToFindPrintedGrid(): void
    {
        $view = (string) file_get_contents(dirname(__DIR__, 4) . '/app/Tabletop/Views/chamber.php');
        self::assertStringContainsString('data-detect-grid', $view);
        self::assertStringContainsString('Find Printed Grid', $view);
        self::assertStringContainsString('data-grid-registration-status', $view);
    }

    public function testRegistrationReadsArtworkPeriodicityOnBothAxes(): void
    {
        $js = (string) file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertStringContainsString('const detectPrintedGrid = async () =>', $js);
        self::assertStringContainsString('const axisResponse = (vertical) =>', $js);
        self::assertStringContainsString('const axisComb = (response, spacingCanvas) =>', $js);
        self::assertStringContainsString('const xResponse = axisResponse(true)', $js);
        self::assertStringContainsString('const yResponse = axisResponse(false)', $js);
    }

    public function testDetectedRegistrationIsPreviewOnlyUntilExistingSaveGridRoute(): void
    {
        $js = (string) file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertStringContainsString('preview only', $js);
        self::assertStringContainsString('Press Save Grid to make it authoritative.', $js);
        self::assertStringContainsString("request('gmrt_calibrate_grid'", $js);
        self::assertStringNotContainsString('gmrt_detect_grid', $js);
    }

    public function testRegistrationPreservesNearbyEquivalentOffsetAndCanFailConservatively(): void
    {
        $js = (string) file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertStringContainsString('nearestEquivalentOffset', $js);
        self::assertStringContainsString('best.score < .72', $js);
        self::assertStringContainsString('could not find a reliable faint printed square grid', $js);
    }

    public function testPhaseIsDocumentedAndVersioned(): void
    {
        $roadmap = (string) file_get_contents(dirname(__DIR__, 4) . '/ROADMAP.md');
        $phase = (string) file_get_contents(dirname(__DIR__, 4) . '/docs/Roadmap/PHASE-IV.30.1F.md');
        $plugin = (string) file_get_contents(dirname(__DIR__, 4) . '/great-marketrealm-tabletop.php');
        self::assertStringContainsString('IV.30.1F — The Cartographer\'s Registration', $roadmap);
        self::assertStringContainsString('Hostile misaligned-grid benchmark', $phase);
        self::assertStringContainsString('preview-only', $phase);
        self::assertStringContainsString('0.32.0-alpha.1', $plugin);
    }
}
