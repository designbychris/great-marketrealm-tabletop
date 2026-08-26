<?php
declare(strict_types=1);
namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Http;
use PHPUnit\Framework\TestCase;
final class TestTableAjaxRegressionTest extends TestCase
{
 private string $s;
 protected function setUp():void{$this->s=(string)file_get_contents(dirname(__DIR__,4).'/app/Tabletop/Http/TestTableAjaxController.php');}
 public function testEndpointRequiresAuthentication():void{self::assertStringContainsString('is_user_logged_in()',$this->s);self::assertStringContainsString('get_current_user_id()',$this->s);}
 public function testEndpointRequiresTabletopNonce():void{self::assertStringContainsString('check_ajax_referer(',$this->s);self::assertStringContainsString('TabletopAjaxController::NONCE_ACTION',$this->s);}
}
