<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Footsteps;

use PHPUnit\Framework\TestCase;

final class FootstepsThroughTheVeilRegressionTest extends TestCase
{
    public function testMovementRecordsOnlyPlayerControlledCharacterFootsteps(): void
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/app/Tabletop/Footsteps/Services/FootstepTrailRecorder.php');
        self::assertIsString($source);
        self::assertStringContainsString('TableTokenType::CHARACTER', $source);
        self::assertStringContainsString('controllerUserId()', $source);
        self::assertStringContainsString("'angle'", $source);
    }

    public function testTrailsAreBoundedAndProjectedThroughViewerFog(): void
    {
        $repository = file_get_contents(dirname(__DIR__, 4) . '/app/Tabletop/Footsteps/Repositories/WordPressFootstepTrailRepository.php');
        $projector = file_get_contents(dirname(__DIR__, 4) . '/app/Tabletop/Footsteps/Presentation/FootstepTrailProjector.php');
        self::assertIsString($repository);
        self::assertIsString($projector);
        self::assertStringContainsString('MAX_PER_TOKEN = 12', $repository);
        self::assertStringContainsString('$owner === $viewerUserId', $projector);
        self::assertStringContainsString('isset($visible[$cellKey])', $projector);
        self::assertStringContainsString('isset($explored[$cellKey])', $projector);
        self::assertStringContainsString("'memory'", $projector);
    }

    public function testLongMovementLeavesSeveralDarkerBoundedFootprints(): void
    {
        $recorder = file_get_contents(dirname(__DIR__, 4) . '/app/Tabletop/Footsteps/Services/FootstepTrailRecorder.php');
        $projector = file_get_contents(dirname(__DIR__, 4) . '/app/Tabletop/Footsteps/Presentation/FootstepTrailProjector.php');
        $css = file_get_contents(dirname(__DIR__, 4) . '/assets/css/tabletop.css');
        self::assertIsString($recorder);
        self::assertIsString($projector);
        self::assertIsString($css);
        self::assertStringContainsString('ceil($distanceSquares / 2)', $recorder);
        self::assertStringContainsString('min(6,', $recorder);
        self::assertStringContainsString('$scene->gridSize()', $recorder);
        self::assertStringContainsString('0.72 - ($age * 0.04)', $projector);
        self::assertStringContainsString('brightness(.62)', $css);
    }

    public function testLivingTableRendersFellowshipColouredFootstepsUnderTheVeil(): void
    {
        $view = file_get_contents(dirname(__DIR__, 4) . '/app/Tabletop/Views/chamber.php');
        $script = file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        $css = file_get_contents(dirname(__DIR__, 4) . '/assets/css/tabletop.css');
        self::assertIsString($view);
        self::assertIsString($script);
        self::assertIsString($css);
        self::assertStringContainsString('data-footstep-layer', $view);
        self::assertStringContainsString('renderFootsteps(state.footsteps || [])', $script);
        self::assertStringContainsString('--gmrt-step-colour', $script);
        self::assertStringContainsString('.gmrt-footstep.is-memory', $css);
        self::assertStringContainsString('z-index: 6', $css);
    }
}
