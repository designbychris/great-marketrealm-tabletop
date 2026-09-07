<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\SceneObjects;

use PHPUnit\Framework\TestCase;

final class FurnitureSvgPaintRegressionTest extends TestCase
{
    private function source(string $path): string
    {
        return (string) file_get_contents(dirname(__DIR__, 4) . '/' . $path);
    }

    public function test_inkscape_style_paint_is_promoted_to_safe_svg_attributes(): void
    {
        $svg = $this->source('app/Tabletop/SceneObjects/FurnitureSvgSanitizer.php');

        self::assertStringContainsString('private const STYLE_PROPERTIES', $svg);
        self::assertStringContainsString("'fill', 'fill-opacity', 'stroke', 'stroke-width'", $svg);
        self::assertStringContainsString('$this->promoteSafeStyle($node, $node->getAttribute(\'style\'));', $svg);
        self::assertStringContainsString('$node->setAttribute($property, $value);', $svg);
    }

    public function test_style_attribute_itself_remains_outside_the_allowed_attribute_list(): void
    {
        $svg = $this->source('app/Tabletop/SceneObjects/FurnitureSvgSanitizer.php');

        $attributeBlock = substr(
            $svg,
            (int) strpos($svg, 'private const ATTRIBUTES'),
            (int) strpos($svg, 'public function sanitize') - (int) strpos($svg, 'private const ATTRIBUTES')
        );

        self::assertStringNotContainsString("'style'", $attributeBlock);
        self::assertStringContainsString('promoteSafeStyle', $svg);
    }

    public function test_active_css_values_are_still_rejected(): void
    {
        $svg = $this->source('app/Tabletop/SceneObjects/FurnitureSvgSanitizer.php');

        self::assertStringContainsString('javascript:', $svg);
        self::assertStringContainsString('url\\s*\\(', $svg);
        self::assertStringContainsString('expression\\s*\\(', $svg);
        self::assertStringNotContainsString("'foreignObject'", $svg);
    }

    public function test_common_inkscape_hex_fills_are_supported(): void
    {
        $svg = $this->source('app/Tabletop/SceneObjects/FurnitureSvgSanitizer.php');

        self::assertStringContainsString('#[0-9a-f]{3,8}', $svg);
        self::assertStringContainsString("in_array(\$property, ['fill', 'stroke'], true)", $svg);
    }
}
