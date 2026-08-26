<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Battle\Models;

use GreatMarketrealmTabletop\Tabletop\Battle\Models\DamageType;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class DamageTypeTest extends TestCase
{
    public function testCanonicalDamageTypesAreStable(): void
    {
        self::assertContains('slashing', DamageType::all());
        self::assertContains('fire', DamageType::all());
        self::assertContains('poison', DamageType::all());
        self::assertCount(13, DamageType::all());
    }

    public function testUnsupportedDamageTypeIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        DamageType::assert('mildly-inconvenient');
    }
}
