<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tables\Tokens\Models;

use GreatMarketrealmTabletop\Tables\Tokens\Models\TableTokenType;
use GreatMarketrealmTabletop\Tables\Tokens\Models\TableTokenVisibility;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class TableTokenVocabularyTest extends TestCase
{
    public function testInitialTokenTypesAreCharacterCreatureAndObject(): void
    {
        self::assertSame(
            ['character', 'creature', 'object'],
            TableTokenType::all()
        );
    }

    public function testVisibilityIsVisibleOrHidden(): void
    {
        self::assertSame(
            ['visible', 'hidden'],
            TableTokenVisibility::all()
        );
    }

    public function testUnknownTokenTypeIsRejected(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        TableTokenType::assert('sentient-cutlery');
    }

    public function testUnknownVisibilityIsRejected(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        TableTokenVisibility::assert(
            'mostly-behind-a-pie'
        );
    }
}
