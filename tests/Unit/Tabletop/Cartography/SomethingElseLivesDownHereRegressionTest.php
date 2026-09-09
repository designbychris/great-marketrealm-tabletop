<?php

declare(strict_types=1);
namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Cartography;
use PHPUnit\Framework\TestCase;
final class SomethingElseLivesDownHereRegressionTest extends TestCase
{
    public function test_both_forge_surfaces_offer_population(): void { $v=$this->s('app/Tabletop/Views/chamber.php');self::assertStringContainsString('data-atlas-forge-populate',$v);self::assertStringContainsString('data-dungeon-forge-populate',$v); }
    public function test_forge_persists_hidden_ordinary_bestiary_occupants(): void { $p=$this->s('app/Tabletop/Http/DungeonForgeAjaxController.php');self::assertStringContainsString('$this->occupants->plan($plan)',$p);self::assertStringContainsString("'forge_occupants' => \$forgeOccupants",$p);self::assertStringContainsString("\$draft['quantity'], true",$p); }
    private function s(string $rel):string{return (string)file_get_contents(dirname(__DIR__,4).'/'.$rel);}
}
