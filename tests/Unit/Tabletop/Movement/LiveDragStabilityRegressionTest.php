<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Movement;

use PHPUnit\Framework\TestCase;

final class LiveDragStabilityRegressionTest extends TestCase
{
    public function test_live_refresh_cannot_repaint_a_token_back_to_its_old_position_mid_drag(): void
    {
        $js = (string) file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');

        self::assertStringContainsString('let tokenDragInProgress = false;', $js);
        self::assertStringContainsString('tokenDragInProgress = true;', $js);
        self::assertStringContainsString('await moveSelected(point.x, point.y);', $js);
        self::assertStringContainsString('tokenDragInProgress = false;', $js);
        self::assertStringContainsString('if (tokenDragInProgress || sceneObjectDrag) {', $js);
    }
}
