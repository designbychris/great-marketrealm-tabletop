<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tables\Tokens\Models;

use DateTimeImmutable;
use GreatMarketrealmTabletop\Tables\Tokens\Models\TableToken;
use GreatMarketrealmTabletop\Tables\Tokens\Models\TableTokenType;
use GreatMarketrealmTabletop\Tables\Tokens\Models\TableTokenVisibility;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class TableTokenTest extends TestCase
{
    public function testCharacterTokenStoresOpaqueSourceAndController(): void
    {
        $token = $this->character();

        self::assertSame('token-1', $token->id());
        self::assertSame('table-1', $token->tableId());
        self::assertSame('scene-1', $token->sceneId());
        self::assertSame('Sir Pie', $token->label());
        self::assertSame('character', $token->type());
        self::assertSame(
            'gmrc-character-27',
            $token->sourceReference()
        );
        self::assertSame(84, $token->controllerUserId());
        self::assertSame(.25, $token->x());
        self::assertSame(.75, $token->y());
        self::assertSame(1.0, $token->widthUnits());
        self::assertSame(1.0, $token->heightUnits());
        self::assertTrue($token->isVisible());
    }

    public function testTokenMayMoveResizeHideAndShow(): void
    {
        $token = $this->character();

        $token->move(.5, .4);
        $token->resize(2, 2);
        $token->hide();

        self::assertSame(.5, $token->x());
        self::assertSame(.4, $token->y());
        self::assertSame(2.0, $token->widthUnits());
        self::assertSame(2.0, $token->heightUnits());
        self::assertSame(
            TableTokenVisibility::HIDDEN,
            $token->visibility()
        );

        $token->show();
        self::assertTrue($token->isVisible());
    }

    public function testCharacterAndCreatureRequireSourceReferences(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        TableToken::create(
            'token-1',
            'table-1',
            'scene-1',
            'Unknown Hero',
            TableTokenType::CHARACTER,
            null,
            84,
            .5,
            .5,
            1,
            1,
            TableTokenVisibility::VISIBLE,
            new DateTimeImmutable()
        );
    }

    public function testObjectTokenMayHaveNoExternalSource(): void
    {
        $token = TableToken::create(
            'token-1',
            'table-1',
            'scene-1',
            'Barrel',
            TableTokenType::OBJECT,
            null,
            null,
            .5,
            .5,
            1,
            1,
            TableTokenVisibility::VISIBLE,
            new DateTimeImmutable()
        );

        self::assertNull(
            $token->sourceReference()
        );
        self::assertNull(
            $token->controllerUserId()
        );
    }

    public function testCoordinatesMustRemainNormalised(): void
    {
        $token = $this->character();

        $this->expectException(
            InvalidArgumentException::class
        );

        $token->move(-.1, .5);
    }

    public function testFootprintMustRemainPositive(): void
    {
        $token = $this->character();

        $this->expectException(
            InvalidArgumentException::class
        );

        $token->resize(0, 1);
    }

    public function testTokenRoundTripsPersistentRecord(): void
    {
        $token = $this->character();
        $token->hide();
        $token->resize(2, 1);

        $restored = TableToken::reconstitute(
            $token->toArray()
        );

        self::assertSame(
            $token->toArray(),
            $restored->toArray()
        );
    }

    private function character(): TableToken
    {
        return TableToken::create(
            'token-1',
            'table-1',
            'scene-1',
            'Sir Pie',
            TableTokenType::CHARACTER,
            'gmrc-character-27',
            84,
            .25,
            .75,
            1,
            1,
            TableTokenVisibility::VISIBLE,
            new DateTimeImmutable(
                '2026-08-26T10:00:00+01:00'
            )
        );
    }
}
