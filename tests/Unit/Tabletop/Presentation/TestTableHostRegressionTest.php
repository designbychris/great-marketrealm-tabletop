<?php
declare(strict_types=1);
namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Presentation;
use PHPUnit\Framework\TestCase;
final class TestTableHostRegressionTest extends TestCase
{
 public function testEmptyHostOffersTestTableButton():void{$s=(string)file_get_contents(dirname(__DIR__,4).'/app/Tabletop/Views/chamber.php');self::assertStringContainsString('data-prepare-test-table',$s);self::assertStringContainsString('Prepare Test Table',$s);}
 public function testClientRedirectsIntoPreparedTable():void{$s=(string)file_get_contents(dirname(__DIR__,4).'/assets/js/tabletop.js');self::assertStringContainsString('gmrt_prepare_test_table',$s);self::assertStringContainsString("url.searchParams.set('table', data.table_id)",$s);}

    public function testEmptyHostBootsBeforeBattlemapGuard(): void
    {
        $source = (string) file_get_contents(
            dirname(__DIR__, 4)
                . '/assets/js/tabletop.js'
        );

        $handler = strpos(
            $source,
            "const prepareTestTableButton"
        );
        $boardGuard = strpos(
            $source,
            "if (!board)"
        );

        self::assertIsInt($handler);
        self::assertIsInt($boardGuard);
        self::assertLessThan(
            $boardGuard,
            $handler,
            'The empty-host button must initialize before board-only code.'
        );
    }

}
