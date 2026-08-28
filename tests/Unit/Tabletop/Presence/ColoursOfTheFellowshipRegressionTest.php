<?php

declare(strict_types=1);
namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Presence;
use PHPUnit\Framework\TestCase;
final class ColoursOfTheFellowshipRegressionTest extends TestCase
{
    private function root(): string { return dirname(__DIR__, 4); }
    public function testMarketrealmPaletteAndPersistentMembershipColourExist(): void { $palette=file_get_contents($this->root().'/app/Tables/Memberships/Models/TableColourPalette.php'); $member=file_get_contents($this->root().'/app/Tables/Memberships/Models/TableMember.php'); self::assertStringContainsString("'aubergine'",$palette); self::assertStringContainsString("'golden-cheddar'",$palette); self::assertStringContainsString("'market-teal'",$palette); self::assertStringContainsString('chooseTableColour',$member); self::assertStringContainsString("'table_colour'",$member); }
    public function testColourChoiceIsAuthenticatedAndServerValidated(): void { $c=file_get_contents($this->root().'/app/Tabletop/Http/TableColourAjaxController.php'); $p=file_get_contents($this->root().'/app/Tabletop/TabletopServiceProvider.php'); self::assertStringContainsString('is_user_logged_in()',$c); self::assertStringContainsString('check_ajax_referer',$c); self::assertStringContainsString('TableColourPalette::has',$c); self::assertStringContainsString('gmrt_choose_table_colour',$p); }
    public function testColourIdentityFlowsThroughGatheringChronicleSatchelAndTokens(): void { $v=file_get_contents($this->root().'/app/Tabletop/Views/chamber.php'); $j=file_get_contents($this->root().'/assets/js/tabletop.js'); $css=file_get_contents($this->root().'/assets/css/tabletop.css'); self::assertStringContainsString('Your Fellowship Ribbon',$v); self::assertStringContainsString('data-table-colour',$v); self::assertStringContainsString("entry.table_colour",$j); self::assertStringContainsString("token.table_colour_hex",$j); self::assertStringContainsString('--gmrt-fellowship-colour',$css); }
}
