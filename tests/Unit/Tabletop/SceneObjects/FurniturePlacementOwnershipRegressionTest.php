<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\SceneObjects;

use PHPUnit\Framework\TestCase;

final class FurniturePlacementOwnershipRegressionTest extends TestCase
{
    private function root(string $path): string
    {
        return dirname(__DIR__, 4) . '/' . ltrim($path, '/');
    }

    public function test_furniture_owns_the_pointer_before_battlefield_interactions_can_swallow_it(): void
    {
        $js = file_get_contents($this->root('assets/js/tabletop.js'));
        $start = strpos($js, '// Furniture placement owns the next battlefield pointer in capture phase.');
        $end = strpos($js, 'async function replaceLifecycle', $start === false ? 0 : $start);

        self::assertNotFalse($start);
        self::assertNotFalse($end);

        $placement = substr($js, $start, $end - $start);

        self::assertStringContainsString("board?.addEventListener('pointerdown', async (event) => {", $placement);
        self::assertStringContainsString('if (event.button !== 0) return;', $placement);
        self::assertStringContainsString('event.stopImmediatePropagation();', $placement);
        self::assertStringContainsString('const point = furniturePoint(coordinatesFromPointer(event));', $placement);
        self::assertStringNotContainsString("board?.addEventListener('click', async (event) => {", $placement);

        $lensGuard = substr($js, strpos($js, "lensStage?.addEventListener('pointerdown'"), 650);
        self::assertStringContainsString('|| furniturePlacement', $lensGuard);
    }
}
