<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tables\Scenes\Models;

use GreatMarketrealmTabletop\Tables\Scenes\Models\GridType;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class GridTypeTest extends TestCase
{
    public function testInitialGridVocabularyIsDeliberatelySmall(): void
    {
        self::assertSame(['square', 'none'], GridType::all());
    }

    public function testUnknownGridTypeIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        GridType::assert('hex');
    }
}
