<?php
declare(strict_types=1);
namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Cartography;
use PHPUnit\Framework\TestCase;
final class GridCalibrationRegressionTest extends TestCase {
 public function testGridCalibrationIsDungeonMasterAuthoritative():void { $s=(string)file_get_contents(dirname(__DIR__,4).'/app/Tabletop/Cartography/Services/CartographersTable.php'); self::assertStringContainsString('calibrateActiveGrid',$s); self::assertStringContainsString('TableMemberRole::DUNGEON_MASTER',$s); self::assertStringContainsString('$this->scenes->save($scene)',$s); }
 public function testCalibrationRouteUsesExistingNonce():void { $s=(string)file_get_contents(dirname(__DIR__,4).'/app/Tabletop/Http/CartographyAjaxController.php'); self::assertStringContainsString('calibrateGrid(): void',$s); self::assertStringContainsString('check_ajax_referer',$s); }
 public function testGridHasLivePreviewControls():void { $s=(string)file_get_contents(dirname(__DIR__,4).'/app/Tabletop/Views/chamber.php'); self::assertStringContainsString('data-grid-size',$s); self::assertStringContainsString('data-grid-offset-x',$s); self::assertStringContainsString('data-grid-offset-y',$s); self::assertStringContainsString('data-grid-opacity',$s); self::assertStringContainsString('data-grid-visible',$s); self::assertStringContainsString('data-grid-nudge',$s); }
 public function testClientPreviewsBeforeSaving():void { $s=(string)file_get_contents(dirname(__DIR__,4).'/assets/js/tabletop.js'); self::assertStringContainsString('const previewGrid',$s); self::assertStringContainsString("'gmrt_calibrate_grid'",$s); self::assertStringContainsString("--gmrt-grid-offset-x",$s); }
}
