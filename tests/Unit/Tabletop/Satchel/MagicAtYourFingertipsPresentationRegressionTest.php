<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Satchel;

use PHPUnit\Framework\TestCase;

final class MagicAtYourFingertipsPresentationRegressionTest extends TestCase
{
    public function testSpellPouchExposesOnlyAuthoritativeSpellActionIdentifiers(): void
    {
        $root = dirname(__DIR__, 4);
        $view = file_get_contents($root . '/app/Tabletop/Views/chamber.php');
        $javascript = file_get_contents($root . '/assets/js/tabletop.js');
        $provider = file_get_contents($root . '/app/Tabletop/TabletopServiceProvider.php');
        $controller = file_get_contents($root . '/app/Tabletop/Http/SpellPouchAjaxController.php');

        self::assertStringContainsString('data-spell-action="attack"', $view);
        self::assertStringContainsString('data-spell-action="damage"', $view);
        self::assertStringContainsString('data-spell-action="healing"', $view);
        self::assertStringContainsString("request('gmrt_spell_pouch_roll'", $javascript);
        self::assertStringContainsString('spell_action:', $javascript);
        self::assertStringContainsString('spell_id:', $javascript);
        self::assertStringContainsString("'wp_ajax_gmrt_spell_pouch_roll'", $provider);
        self::assertStringNotContainsString("\$_POST['modifier']", $controller);
        self::assertStringNotContainsString("\$_POST['formula']", $controller);
        self::assertStringNotContainsString("\$_POST['spell_attack']", $controller);
    }
}
