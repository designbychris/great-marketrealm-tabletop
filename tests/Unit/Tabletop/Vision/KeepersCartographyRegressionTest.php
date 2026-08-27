<?php

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Vision;

use PHPUnit\Framework\TestCase;

final class KeepersCartographyRegressionTest extends TestCase
{
    private function script(): string
    {
        return (string) file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
    }

    public function testWallToolKeepsTheLastEndpointAsTheNextAnchor(): void
    {
        self::assertStringContainsString("visionStart = point;", $this->script());
        self::assertStringContainsString('Continue from this anchor', $this->script());
    }

    public function testCartographyShowsASnappedLivePreview(): void
    {
        $script = $this->script();
        self::assertStringContainsString("board.addEventListener('pointermove'", $script);
        self::assertStringContainsString("'is-preview'", $script);
    }

    public function testCartographySupportsSelectingAndHighlightingSegments(): void
    {
        $script = $this->script();
        self::assertStringContainsString('selectedVisionBarrier', $script);
        self::assertStringContainsString("'is-selected'", $script);
    }

    public function testCartographyCanUndoTheMostRecentSegment(): void
    {
        $script = $this->script();
        self::assertStringContainsString("document.querySelector('[data-vision-undo]')", $script);
        self::assertStringContainsString('Last cartography segment undone.', $script);
    }

    public function testDoorGuidanceExplainsWallFraming(): void
    {
        self::assertStringContainsString(
            'Frame both sides with walls so sight cannot travel around it.',
            $this->script()
        );
    }
}
