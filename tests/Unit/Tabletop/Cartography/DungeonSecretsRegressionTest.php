<?php

declare(strict_types=1);
namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Cartography;
use PHPUnit\Framework\TestCase;
final class DungeonSecretsRegressionTest extends TestCase
{
    public function test_both_forge_surfaces_offer_secrets():void{$v=$this->s('app/Tabletop/Views/chamber.php');self::assertStringContainsString('data-atlas-forge-secrets',$v);self::assertStringContainsString('data-dungeon-forge-secrets',$v);}
    public function test_player_boundary_removes_unrevealed_secrets_and_concealed_door_ink():void{$v=$this->s('app/Tabletop/Views/chamber.php');self::assertStringContainsString('unrevealed Forge secrets never cross the Player presentation boundary',$v);self::assertStringContainsString("\$dungeonForge['secrets']=\$visibleSecrets",$v);self::assertStringContainsString('$hiddenDoorIndexes',$v);}
    public function test_keeper_has_explicit_persistent_reveal_action():void{$p=$this->s('app/Tabletop/Http/DungeonForgeAjaxController.php');$js=$this->s('assets/js/tabletop.js');self::assertStringContainsString('function revealSecret()', $p);self::assertStringContainsString("['revealed']=true",$p);self::assertStringContainsString('gmrt_reveal_forge_secret',$js);}
    private function s(string $rel):string{return (string)file_get_contents(dirname(__DIR__,4).'/'.$rel);}
}
