<?php

declare(strict_types=1);
namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Satchel;
use PHPUnit\Framework\TestCase;
final class SpellPouchPresentationRegressionTest extends TestCase
{
 public function testSatchelUnfurlsAndSpellPouchUsesCompanionProjection(): void
 {
  $root=dirname(__DIR__,4); $view=file_get_contents($root.'/app/Tabletop/Views/chamber.php'); $css=file_get_contents($root.'/assets/css/tabletop.css');
  self::assertStringContainsString('Spell Pouch', $view); self::assertStringContainsString("\$adventurerPlay['spellcasting']", $view);
  self::assertStringContainsString('top:var(--gmrt-satchel-top,7.75rem);bottom:var(--gmrt-satchel-bottom,1rem)', $css); self::assertStringContainsString('height:100%;max-height:none;overflow-y:auto;overflow-x:hidden', $css); self::assertStringContainsString('position:sticky', $css);
 }
}
