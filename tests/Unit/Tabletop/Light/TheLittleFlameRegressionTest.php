<?php
declare(strict_types=1);
namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Light;
use PHPUnit\Framework\TestCase;
final class TheLittleFlameRegressionTest extends TestCase
{
    public function testDroppedTorchUsesPixelFlameInsteadOfEmoji(): void
    {
        $root=dirname(__DIR__,4); $js=(string)file_get_contents($root.'/assets/js/tabletop.js');
        self::assertStringContainsString("flame.className = 'gmrt-pixel-flame'",$js);
        self::assertStringContainsString("Dropped burning torch",$js);
        self::assertStringNotContainsString("glow.textContent = '🔥'",$js);
    }
    public function testLittleFlameFlickersButRespectsReducedMotion(): void
    {
        $root=dirname(__DIR__,4); $css=(string)file_get_contents($root.'/assets/css/tabletop.css');
        self::assertStringContainsString('@keyframes gmrt-pixel-flame-flicker',$css);
        self::assertStringContainsString('@keyframes gmrt-pixel-ember-pop',$css);
        self::assertStringContainsString('steps(2,end) infinite',$css);
        self::assertStringContainsString('prefers-reduced-motion: reduce',$css);
        self::assertStringContainsString('.gmrt-pixel-flame > em { display:none; }',$css);
    }
}
