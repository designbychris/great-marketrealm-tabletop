<?php
declare(strict_types=1);
namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Cartography;
use PHPUnit\Framework\TestCase;
final class CartographersLensRegressionTest extends TestCase
{
    public function testChamberExposesLensControls(): void
    {
        $s=(string)file_get_contents(dirname(__DIR__,4).'/app/Tabletop/Views/chamber.php');
        self::assertStringContainsString('data-cartographers-lens',$s);
        self::assertStringContainsString('data-lens-zoom-out',$s);
        self::assertStringContainsString('data-lens-zoom-in',$s);
        self::assertStringContainsString('data-lens-fit',$s);
        self::assertStringContainsString('data-lens-reset',$s);
        self::assertStringContainsString('data-lens-stage',$s);
    }
    public function testClientSupportsZoomFitResetAndPan(): void
    {
        $s=(string)file_get_contents(dirname(__DIR__,4).'/assets/js/tabletop.js');
        self::assertStringContainsString('const zoomLens',$s);
        self::assertStringContainsString('const fitLens',$s);
        self::assertStringContainsString('const resetLens',$s);
        self::assertStringContainsString("'pointermove'",$s);
        self::assertStringContainsString('scale(${lens.scale})',$s);
    }
    public function testLensDoesNotCreateServerMutationRoute(): void
    {
        $s=(string)file_get_contents(dirname(__DIR__,4).'/assets/js/tabletop.js');
        self::assertStringNotContainsString('gmrt_save_lens',$s);
        self::assertStringNotContainsString('gmrt_pan_map',$s);
        self::assertStringNotContainsString('gmrt_zoom_map',$s);
    }
    public function testLensTransformsWholeBattlefieldCoordinateSpace(): void
    {
        $s=(string)file_get_contents(dirname(__DIR__,4).'/assets/css/tabletop.css');
        self::assertStringContainsString('.gmrt-board__lens-stage .gmrt-board__viewport',$s);
        self::assertStringContainsString('transform-origin:0 0',$s);
        self::assertStringContainsString('overflow:hidden',$s);
    }
}
