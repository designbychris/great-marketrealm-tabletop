<?php
declare(strict_types=1);
namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Cartography;
use PHPUnit\Framework\TestCase;
final class GridSavePersistenceRegressionTest extends TestCase
{
    public function testSaveUsesServerResponseAndVisibleCartographerFeedback():void
    {
        $s=(string)file_get_contents(dirname(__DIR__,4).'/assets/js/tabletop.js');
        self::assertStringContainsString("const data = await request('gmrt_calibrate_grid'",$s);
        self::assertStringContainsString('const saved = data.grid || {}',$s);
        self::assertStringContainsString('Grid saved ·',$s);
        self::assertStringContainsString('originalGrid.size = gridSize.value',$s);
    }
    public function testExplicitFalseVisibilityIsSentAndParsed():void
    {
        $js=(string)file_get_contents(dirname(__DIR__,4).'/assets/js/tabletop.js');
        $php=(string)file_get_contents(dirname(__DIR__,4).'/app/Tabletop/Http/CartographyAjaxController.php');
        self::assertStringContainsString("gridVisible.checked ? '1' : '0'",$js);
        self::assertStringContainsString('FILTER_VALIDATE_BOOLEAN',$php);
    }
}
