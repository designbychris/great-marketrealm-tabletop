<?php
declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Cartography\Models;

use GreatMarketrealmTabletop\Tabletop\Cartography\Models\BattlemapImage;
use PHPUnit\Framework\TestCase;

final class BattlemapImageTest extends TestCase
{
    public function testBattlemapImageSerializesMediaIdentity(): void
    {
        $image = new BattlemapImage(
            44,
            1920,
            1080,
            'https://example.test/map.png'
        );

        self::assertSame(44, $image->attachmentId());
        self::assertSame(1920, $image->width());
        self::assertSame(1080, $image->height());
        self::assertSame(
            'https://example.test/map.png',
            $image->url()
        );
    }
}
