<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Movement;

require_once __DIR__ . '/MovementTestDoubles.php';

use DateTimeImmutable;
use GreatMarketrealmTabletop\Tabletop\Movement\Exceptions\StaleTokenRevision;
use GreatMarketrealmTabletop\Tabletop\Movement\Exceptions\TabletopMovementDenied;
use GreatMarketrealmTabletop\Tabletop\Movement\Services\TabletopMovement;
use GreatMarketrealmTabletop\Tabletop\Movement\Services\TabletopMovementPolicy;
use GreatMarketrealmTabletop\Tables\Memberships\Models\TableMember;
use GreatMarketrealmTabletop\Tables\Models\Table;
use GreatMarketrealmTabletop\Tables\Scenes\Models\GridType;
use GreatMarketrealmTabletop\Tables\Scenes\Models\TableScene;
use GreatMarketrealmTabletop\Tables\Tokens\Models\TableToken;
use GreatMarketrealmTabletop\Tables\Tokens\Models\TableTokenType;
use GreatMarketrealmTabletop\Tables\Tokens\Models\TableTokenVisibility;
use PHPUnit\Framework\TestCase;

final class TabletopMovementTest extends TestCase
{
    private MovementTables $tables;
    private MovementMembers $members;
    private MovementScenes $scenes;
    private MovementTokens $tokens;
    private TabletopMovement $movement;

    protected function setUp(): void
    {
        $now = new DateTimeImmutable(
            '2026-08-26T10:00:00+01:00'
        );

        $this->tables = new MovementTables();
        $this->members = new MovementMembers();
        $this->scenes = new MovementScenes();
        $this->tokens = new MovementTokens();

        $this->tables->save(
            Table::prepare(
                'table-1',
                42,
                'Living Table',
                $now
            )
        );

        $this->members->save(
            TableMember::dungeonMaster(
                'table-1',
                42,
                $now
            )
        );

        $player = TableMember::invitePlayer(
            'table-1',
            84,
            $now
        );
        $player->join($now);
        $player->selectCompanionCharacter(
            'gmrc-character-27'
        );
        $this->members->save($player);

        $scene = TableScene::create(
            'scene-1',
            'table-1',
            'Cold Vault',
            1,
            1600,
            900,
            GridType::SQUARE,
            50,
            $now
        );
        $scene->activate();
        $this->scenes->save($scene);

        $this->tokens->save(
            TableToken::create(
                'token-hero',
                'table-1',
                'scene-1',
                'Sir Pie',
                TableTokenType::CHARACTER,
                'gmrc-character-27',
                84,
                .25,
                .25,
                1,
                1,
                TableTokenVisibility::VISIBLE,
                $now
            )
        );

        $this->tokens->save(
            TableToken::create(
                'token-monster',
                'table-1',
                'scene-1',
                'Gravy Golem',
                TableTokenType::CREATURE,
                'gmrc-creature-gravy-golem',
                null,
                .75,
                .75,
                2,
                2,
                TableTokenVisibility::VISIBLE,
                $now
            )
        );

        $this->movement = new TabletopMovement(
            $this->tables,
            $this->members,
            $this->scenes,
            $this->tokens,
            new TabletopMovementPolicy()
        );
    }

    public function testDungeonMasterMayMoveCreature(): void
    {
        $token = $this->movement->move(
            'table-1',
            42,
            'token-monster',
            .6,
            .4,
            1
        );

        self::assertSame(.6, $token->x());
        self::assertSame(.4, $token->y());
        self::assertSame(2, $token->revision());
    }

    public function testPlayerMayMoveAssignedCharacter(): void
    {
        $token = $this->movement->move(
            'table-1',
            84,
            'token-hero',
            .4,
            .5,
            1
        );

        self::assertSame(.4, $token->x());
        self::assertSame(.5, $token->y());
    }

    public function testPlayerCannotMoveCreature(): void
    {
        $this->expectException(
            TabletopMovementDenied::class
        );

        $this->movement->move(
            'table-1',
            84,
            'token-monster',
            .4,
            .5,
            1
        );
    }

    public function testStaleRevisionIsRejected(): void
    {
        $this->movement->move(
            'table-1',
            42,
            'token-monster',
            .6,
            .4,
            1
        );

        $this->expectException(
            StaleTokenRevision::class
        );

        $this->movement->move(
            'table-1',
            42,
            'token-monster',
            .7,
            .4,
            1
        );
    }

    public function testInactiveSceneTokenCannotMove(): void
    {
        $scene = $this->scenes->find(
            'table-1',
            'scene-1'
        );

        self::assertNotNull($scene);
        $scene->deactivate();
        $this->scenes->save($scene);

        $this->expectException(
            TabletopMovementDenied::class
        );

        $this->movement->move(
            'table-1',
            42,
            'token-hero',
            .4,
            .4,
            1
        );
    }
}
