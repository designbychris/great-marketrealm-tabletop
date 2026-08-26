<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Movement;

use DateTimeImmutable;
use GreatMarketrealmTabletop\Tabletop\Movement\Services\TabletopMovementPolicy;
use GreatMarketrealmTabletop\Tables\Memberships\Models\TableMember;
use GreatMarketrealmTabletop\Tables\Tokens\Models\TableToken;
use GreatMarketrealmTabletop\Tables\Tokens\Models\TableTokenType;
use GreatMarketrealmTabletop\Tables\Tokens\Models\TableTokenVisibility;
use PHPUnit\Framework\TestCase;

final class TabletopMovementPolicyTest extends TestCase
{
    public function testDungeonMasterMayMoveAnyActiveToken(): void
    {
        $member = TableMember::dungeonMaster(
            'table-1',
            42,
            new DateTimeImmutable()
        );

        self::assertTrue(
            (new TabletopMovementPolicy())->mayMove(
                $member,
                $this->objectToken()
            )
        );
    }

    public function testPlayerMayMoveMatchingAssignedCharacterToken(): void
    {
        $member = $this->player();
        $member->selectCompanionCharacter(
            'gmrc-character-27'
        );

        self::assertTrue(
            (new TabletopMovementPolicy())->mayMove(
                $member,
                $this->characterToken(
                    'gmrc-character-27',
                    84
                )
            )
        );
    }

    public function testPlayerCannotMoveAnotherPlayersCharacter(): void
    {
        $member = $this->player();
        $member->selectCompanionCharacter(
            'gmrc-character-27'
        );

        self::assertFalse(
            (new TabletopMovementPolicy())->mayMove(
                $member,
                $this->characterToken(
                    'gmrc-character-99',
                    126
                )
            )
        );
    }

    public function testPlayerCannotMoveCreatureOrObjectTokens(): void
    {
        $member = $this->player();
        $member->selectCompanionCharacter(
            'gmrc-character-27'
        );

        self::assertFalse(
            (new TabletopMovementPolicy())->mayMove(
                $member,
                $this->objectToken()
            )
        );
    }

    private function player(): TableMember
    {
        $member = TableMember::invitePlayer(
            'table-1',
            84,
            new DateTimeImmutable()
        );
        $member->join(new DateTimeImmutable());

        return $member;
    }

    private function characterToken(
        string $reference,
        int $controller
    ): TableToken {
        return TableToken::create(
            'token-character',
            'table-1',
            'scene-1',
            'Hero',
            TableTokenType::CHARACTER,
            $reference,
            $controller,
            .5,
            .5,
            1,
            1,
            TableTokenVisibility::VISIBLE,
            new DateTimeImmutable()
        );
    }

    private function objectToken(): TableToken
    {
        return TableToken::create(
            'token-object',
            'table-1',
            'scene-1',
            'Crate',
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
