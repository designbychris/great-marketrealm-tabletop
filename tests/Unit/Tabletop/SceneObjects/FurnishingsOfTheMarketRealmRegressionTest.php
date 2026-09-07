<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\SceneObjects;

use PHPUnit\Framework\TestCase;

final class FurnishingsOfTheMarketRealmRegressionTest extends TestCase
{
    private function source(string $path): string
    {
        return (string) file_get_contents(dirname(__DIR__, 4) . '/' . $path);
    }

    public function test_catalogue_adds_the_second_wave_of_marketrealm_furnishings(): void
    {
        $catalogue = $this->source('app/Tabletop/SceneObjects/FurnitureCatalogue.php');

        foreach (['bed','desk','bench','cupboard','sacks','weapon-rack','market-stall','campfire','stool','rug'] as $kind) {
            self::assertStringContainsString("'" . $kind . "' => \$this->definition(", $catalogue);
        }
    }

    public function test_new_furnishings_keep_explicit_tactical_traits(): void
    {
        $catalogue = $this->source('app/Tabletop/SceneObjects/FurnitureCatalogue.php');

        self::assertStringContainsString("'Bed'", $catalogue);
        self::assertStringContainsString("'Cupboard'", $catalogue);
        self::assertStringContainsString("'Market Stall'", $catalogue);
        self::assertStringContainsString("'Campfire'", $catalogue);
        self::assertStringContainsString("'Rug'", $catalogue);
        self::assertStringContainsString("'mimic_capable' => true", $catalogue);
    }

    public function test_forge_immediately_uses_the_expanded_vocabulary(): void
    {
        $planner = $this->source('app/Tabletop/Cartography/Services/ForgeFurniturePlanner.php');

        foreach (['bed','desk','bench','cupboard','sacks','campfire','stool','rug'] as $kind) {
            self::assertStringContainsString("'kind' => '" . $kind . "'", $planner);
        }
        self::assertStringContainsString("'kind' => 'bookshelf'", $planner);
        self::assertStringContainsString("'kind' => 'chest'", $planner);
    }

    public function test_each_new_furnishing_has_a_scene_pixel_sprite(): void
    {
        $css = $this->source('assets/css/tabletop.css');

        foreach (['bed','desk','bench','cupboard','sacks','weapon-rack','market-stall','campfire','stool','rug'] as $kind) {
            self::assertStringContainsString('.gmrt-scene-object--' . $kind . ' > span', $css);
        }
    }

    public function test_each_new_furnishing_has_a_keeper_palette_sprite(): void
    {
        $css = $this->source('assets/css/tabletop.css');

        foreach (['bed','desk','bench','cupboard','sacks','weapon-rack','market-stall','campfire','stool','rug'] as $kind) {
            self::assertStringContainsString('.gmrt-furniture-choice--' . $kind . ' .gmrt-furniture-choice__sprite', $css);
        }
    }

    public function test_catalogue_driven_palette_needs_no_new_authoring_route(): void
    {
        $view = $this->source('app/Tabletop/Views/chamber.php');
        $catalogue = $this->source('app/Tabletop/SceneObjects/FurnitureCatalogue.php');

        self::assertStringContainsString('$furnitureCatalogue->all()', $view);
        self::assertStringContainsString('data-furniture-kind=', $view);
        self::assertStringNotContainsString('gmrt_add_bed', $catalogue);
        self::assertStringNotContainsString('gmrt_add_market_stall', $catalogue);
    }
}
