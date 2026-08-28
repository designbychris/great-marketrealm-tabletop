<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Satchel;

use PHPUnit\Framework\TestCase;

final class QuickHandsPresentationRegressionTest extends TestCase
{
    public function testSatchelExposesServerAuthoritativeQuickRollControls(): void
    {
        $root = dirname(__DIR__, 4);
        $view = file_get_contents($root . '/app/Tabletop/Views/chamber.php');
        $js = file_get_contents($root . '/assets/js/tabletop.js');

        self::assertIsString($view);
        self::assertIsString($js);
        self::assertStringContainsString('data-quick-roll', $view);
        self::assertStringContainsString("request('gmrt_quick_hands_roll'", $js);
        self::assertStringContainsString("kind: button.dataset.rollKind || ''", $js);
        self::assertStringContainsString("key: button.dataset.rollKey || ''", $js);
    }
}
