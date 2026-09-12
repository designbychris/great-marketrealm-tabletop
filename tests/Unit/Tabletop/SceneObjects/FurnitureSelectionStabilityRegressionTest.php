<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\SceneObjects;

use PHPUnit\Framework\TestCase;

final class FurnitureSelectionStabilityRegressionTest extends TestCase
{
    public function test_plain_selection_does_not_post_a_scene_object_move_or_repaint_the_layer(): void
    {
        $js = (string) file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');

        self::assertStringContainsString('moved: false,', $js);
        self::assertStringContainsString('threshold: 3', $js);
        self::assertStringContainsString('if (!drag.moved) {', $js);
        self::assertStringContainsString("const moved = await submitSceneObjectAction('move'", $js);
    }
}
