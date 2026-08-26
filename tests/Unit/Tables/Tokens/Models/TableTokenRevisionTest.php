<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tables\Tokens\Models;

use DateTimeImmutable;
use GreatMarketrealmTabletop\Tables\Tokens\Models\TableToken;
use GreatMarketrealmTabletop\Tables\Tokens\Models\TableTokenType;
use GreatMarketrealmTabletop\Tables\Tokens\Models\TableTokenVisibility;
use PHPUnit\Framework\TestCase;

final class TableTokenRevisionTest extends TestCase
{
    public function testNewTokenStartsAtRevisionOneAndMovementIncrementsIt(): void
    {
        $token = $this->token();

        self::assertSame(1, $token->revision());

        $token->move(.6, .6);

        self::assertSame(2, $token->revision());
        self::assertSame(
            2,
            $token->toArray()['revision']
        );
    }

    public function testRevisionRoundTripsPersistence(): void
    {
        $token = $this->token();
        $token->move(.6, .6);

        $copy = TableToken::reconstitute(
            $token->toArray()
        );

        self::assertSame(
            2,
            $copy->revision()
        );
    }

    private function token(): TableToken
    {
        return TableToken::create(
            'token-1',
            'table-1',
            'scene-1',
            'Pie',
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
    }
}
