<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Satchel;

use PHPUnit\Framework\TestCase;

final class WeaponsToHandPresentationRegressionTest extends TestCase
{
    public function testSatchelExposesAuthoritativeWeaponAttackAndDamageControls(): void
    {
        $root = dirname(__DIR__, 4);
        $view = file_get_contents($root . '/app/Tabletop/Views/chamber.php');
        $javascript = file_get_contents($root . '/assets/js/tabletop.js');
        $provider = file_get_contents($root . '/app/Tabletop/TabletopServiceProvider.php');
        $controller = file_get_contents($root . '/app/Tabletop/Http/WeaponHandsAjaxController.php');

        self::assertStringContainsString('Weapons to Hand', $view);
        self::assertStringContainsString('data-weapon-action="attack"', $view);
        self::assertStringContainsString('data-weapon-action="damage"', $view);
        self::assertStringContainsString("request('gmrt_weapon_hands_roll'", $javascript);
        self::assertStringContainsString('weapon_action:', $javascript);
        self::assertStringContainsString('attack_id:', $javascript);
        self::assertStringContainsString("'wp_ajax_gmrt_weapon_hands_roll'", $provider);
        self::assertStringNotContainsString("\$_POST['modifier']", $controller);
        self::assertStringNotContainsString("\$_POST['damage_die']", $controller);
    }
}
