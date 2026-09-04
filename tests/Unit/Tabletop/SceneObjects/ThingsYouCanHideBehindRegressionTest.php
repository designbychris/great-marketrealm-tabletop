<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\SceneObjects;

use PHPUnit\Framework\TestCase;

final class ThingsYouCanHideBehindRegressionTest extends TestCase
{
    private function root(string $path): string
    {
        return dirname(__DIR__, 4) . '/' . ltrim($path, '/');
    }

    public function test_catalogue_declares_cover_and_vision_independently_from_movement(): void
    {
        $catalogue = file_get_contents($this->root('app/Tabletop/SceneObjects/FurnitureCatalogue.php'));

        self::assertIsString($catalogue);
        self::assertStringContainsString("'cover' => \$cover", $catalogue);
        self::assertStringContainsString("'blocks_vision' => \$blocksVision", $catalogue);
        self::assertStringContainsString("'half'", $catalogue);
        self::assertStringContainsString("'three_quarters'", $catalogue);
        self::assertStringContainsString("'full'", $catalogue);
        self::assertStringContainsString("'none'", $catalogue);
        self::assertStringContainsString("'Bookshelf'", $catalogue);
        self::assertStringContainsString("true,\n                'full',\n                true", $catalogue);
        self::assertStringNotContainsString("SceneObjectCategory::STRUCTURAL ===", $catalogue);
    }

    public function test_scene_object_projection_backfills_cover_for_older_furniture(): void
    {
        $view = file_get_contents($this->root('app/Tabletop/Views/chamber.php'));

        self::assertIsString($view);
        self::assertStringContainsString("'cover' => (string) (\$definition['cover'] ?? 'none')", $view);
        self::assertStringContainsString("'blocks_vision' => ! empty(\$definition['blocks_vision'])", $view);
        self::assertStringContainsString("\$objectProperties['cover'] ?? (\$objectDefinition['cover'] ?? 'none')", $view);
        self::assertStringContainsString('data-object-cover=', $view);
        self::assertStringContainsString('data-blocks-vision=', $view);
    }

    public function test_targeting_reports_the_strongest_rotated_scene_object_cover(): void
    {
        $js = file_get_contents($this->root('assets/js/tabletop.js'));

        self::assertIsString($js);
        self::assertStringContainsString('function sceneObjectCoverBetween(attacker, target)', $js);
        self::assertStringContainsString('function lineIntersectsPolygon(start, end, polygon)', $js);
        self::assertStringContainsString('rectangleCorners(', $js);
        self::assertStringContainsString("const coverRank = {none: 0, half: 1, three_quarters: 2, full: 3};", $js);
        self::assertStringContainsString("object.dataset.blocksVision === 'true'", $js);
        self::assertStringContainsString("if (cover === 'half') return 'HALF COVER';", $js);
        self::assertStringContainsString("if (cover === 'three_quarters') return '3/4 COVER';", $js);
        self::assertStringContainsString("if (cover === 'full') return 'FULL COVER';", $js);
        self::assertStringContainsString("label += ' · OBSCURED';", $js);
    }

    public function test_cover_phase_does_not_smuggle_in_attack_math_or_light_occlusion(): void
    {
        $js = file_get_contents($this->root('assets/js/tabletop.js'));
        $catalogue = file_get_contents($this->root('app/Tabletop/SceneObjects/FurnitureCatalogue.php'));

        self::assertIsString($js);
        self::assertIsString($catalogue);
        self::assertStringNotContainsString("body.set('cover'", $js);
        self::assertStringNotContainsString('light_occlusion', $catalogue);
        self::assertStringNotContainsString('light_attenuation', $catalogue);
    }
}
