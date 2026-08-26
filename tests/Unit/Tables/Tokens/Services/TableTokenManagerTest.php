<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tables\Tokens\Services;

require_once __DIR__ . '/TokenTestDoubles.php';

use DateTimeImmutable;
use GreatMarketrealmTabletop\Tables\Models\Table;
use GreatMarketrealmTabletop\Tables\Scenes\Models\GridType;
use GreatMarketrealmTabletop\Tables\Scenes\Models\TableScene;
use GreatMarketrealmTabletop\Tables\Tokens\Exceptions\TableTokenException;
use GreatMarketrealmTabletop\Tables\Tokens\Models\TableTokenType;
use GreatMarketrealmTabletop\Tables\Tokens\Models\TableTokenVisibility;
use GreatMarketrealmTabletop\Tables\Tokens\Services\TableTokenManager;
use PHPUnit\Framework\TestCase;

final class TableTokenManagerTest extends TestCase
{
    private TokenTableRepository $tables;
    private TokenSceneRepository $scenes;
    private TokenRepository $tokens;
    private TableTokenManager $manager;

    protected function setUp(): void
    {
        $this->tables =
            new TokenTableRepository();
        $this->scenes =
            new TokenSceneRepository();
        $this->tokens =
            new TokenRepository();

        $this->tables->save(
            Table::prepare(
                'table-1',
                42,
                'The Token Table',
                new DateTimeImmutable(
                    '2026-08-26T10:00:00+01:00'
                )
            )
        );

        $this->scenes->save(
            TableScene::create(
                'scene-1',
                'table-1',
                'Cold Vault',
                11,
                1600,
                900,
                GridType::SQUARE,
                50,
                new DateTimeImmutable(
                    '2026-08-26T10:01:00+01:00'
                )
            )
        );

        $this->manager = new TableTokenManager(
            $this->tables,
            $this->scenes,
            $this->tokens,
            new TokenIds(),
            new TokenClock(
                new DateTimeImmutable(
                    '2026-08-26T10:02:00+01:00'
                )
            )
        );
    }

    public function testCharacterTokenMayBePlacedOnScene(): void
    {
        $token = $this->manager->place(
            'table-1',
            'scene-1',
            'Sir Pie',
            TableTokenType::CHARACTER,
            'gmrc-character-27',
            84,
            .2,
            .3
        );

        self::assertSame(
            'token-1',
            $token->id()
        );
        self::assertSame(
            'gmrc-character-27',
            $token->sourceReference()
        );
        self::assertSame(
            84,
            $token->controllerUserId()
        );
        self::assertCount(
            1,
            $this->manager->forScene(
                'table-1',
                'scene-1'
            )
        );
    }

    public function testCreatureTokenKeepsOpaqueBestiaryReference(): void
    {
        $token = $this->manager->place(
            'table-1',
            'scene-1',
            'Gravy Golem',
            TableTokenType::CREATURE,
            'gmrc-creature-gravy-golem',
            null,
            .6,
            .4,
            2,
            2,
            TableTokenVisibility::HIDDEN
        );

        self::assertSame(
            'gmrc-creature-gravy-golem',
            $token->sourceReference()
        );
        self::assertFalse($token->isVisible());
    }

    public function testTokenMayMoveResizeAndChangeVisibility(): void
    {
        $token = $this->placeObject();

        $moved = $this->manager->move(
            'table-1',
            $token->id(),
            .8,
            .2
        );
        self::assertSame(.8, $moved->x());
        self::assertSame(.2, $moved->y());

        $resized = $this->manager->resize(
            'table-1',
            $token->id(),
            2,
            1
        );
        self::assertSame(
            2.0,
            $resized->widthUnits()
        );

        self::assertFalse(
            $this->manager->hide(
                'table-1',
                $token->id()
            )->isVisible()
        );
        self::assertTrue(
            $this->manager->show(
                'table-1',
                $token->id()
            )->isVisible()
        );
    }

    public function testTokenCannotBePlacedOnAnotherTablesScene(): void
    {
        $this->tables->save(
            Table::prepare(
                'table-2',
                126,
                'Other Table',
                new DateTimeImmutable()
            )
        );

        $this->expectException(
            TableTokenException::class
        );

        $this->manager->place(
            'table-2',
            'scene-1',
            'Sneaky Pie',
            TableTokenType::OBJECT,
            null,
            null,
            .5,
            .5
        );
    }

    public function testEndedTablePreservesButCannotChangeTokens(): void
    {
        $token = $this->placeObject();
        $table = $this->tables->find(
            'table-1'
        );

        self::assertNotNull($table);

        $table->activate(
            new DateTimeImmutable(
                '2026-08-26T10:03:00+01:00'
            )
        );
        $table->end(
            new DateTimeImmutable(
                '2026-08-26T11:00:00+01:00'
            )
        );
        $this->tables->save($table);

        self::assertCount(
            1,
            $this->manager->forScene(
                'table-1',
                'scene-1'
            )
        );

        $this->expectException(
            TableTokenException::class
        );

        $this->manager->move(
            'table-1',
            $token->id(),
            .9,
            .9
        );
    }

    private function placeObject(): \GreatMarketrealmTabletop\Tables\Tokens\Models\TableToken
    {
        return $this->manager->place(
            'table-1',
            'scene-1',
            'Crate',
            TableTokenType::OBJECT,
            null,
            null,
            .4,
            .4
        );
    }
}
