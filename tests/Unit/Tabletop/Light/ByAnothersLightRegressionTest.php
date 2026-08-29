<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Light;

use PHPUnit\Framework\TestCase;

final class ByAnothersLightRegressionTest extends TestCase
{
    public function testWorldLightSourcesRemainSeparateFromViewerVisionOrigins(): void
    {
        $root = dirname(__DIR__, 4);
        $chamber = (string) file_get_contents($root . '/app/Tabletop/Services/TabletopChamber.php');

        self::assertStringContainsString('$worldLightSourceModels = [];', $chamber);
        self::assertStringContainsString('$token->controllerUserId() === $viewerUserId', $chamber);
        self::assertStringContainsString('$worldLightSourceModels[] = $token;', $chamber);
        self::assertStringContainsString('$worldLightSourceModels', $chamber);
    }

    public function testSharedTorchlightIsIntersectedWithViewerLineOfSight(): void
    {
        $root = dirname(__DIR__, 4);
        $fog = (string) file_get_contents($root . '/app/Tabletop/Fog/Services/FogOfWarProjector.php');

        self::assertStringContainsString('array $worldLightSources = []', $fog);
        self::assertStringContainsString('$viewerLineOfSight', $fog);
        self::assertStringContainsString('$mapper->visibleAround($scene, $lightSource, $barriers, 8)', $fog);
        self::assertStringContainsString('array_intersect($illuminated, $viewerLineOfSight)', $fog);
        self::assertStringContainsString("'shared' => true", $fog);
        self::assertStringContainsString("'viewer_carried_light' => \$ownLightSources !== []", $fog);
        self::assertStringNotContainsString('array_merge($visible, $illuminated)', $fog);
    }

    public function testSharedLightDoesNotMakeAnotherAdventurersLanternAppearLit(): void
    {
        $root = dirname(__DIR__, 4);
        $view = (string) file_get_contents($root . '/app/Tabletop/Views/chamber.php');
        $js = (string) file_get_contents($root . '/assets/js/tabletop.js');

        self::assertStringContainsString("! empty(\$fog['viewer_carried_light'])", $view);
        self::assertStringContainsString('data-lantern-state', $view);
        self::assertStringContainsString('projection?.light_sources', $js);
        self::assertStringNotContainsString('light_radius_feet:', $js);
    }
}
