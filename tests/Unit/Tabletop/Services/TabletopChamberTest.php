<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Services;

require_once __DIR__ . '/TabletopTestDoubles.php';

use DateTimeImmutable;
use GreatMarketrealmTabletop\Tabletop\Exceptions\TabletopAccessDenied;
use GreatMarketrealmTabletop\Tabletop\Services\TabletopChamber;
use GreatMarketrealmTabletop\Tables\Memberships\Models\TableMember;
use GreatMarketrealmTabletop\Tables\Models\Table;
use GreatMarketrealmTabletop\Tables\Scenes\Models\GridType;
use GreatMarketrealmTabletop\Tables\Scenes\Models\TableScene;
use GreatMarketrealmTabletop\Tables\Tokens\Models\TableToken;
use GreatMarketrealmTabletop\Tables\Tokens\Models\TableTokenType;
use GreatMarketrealmTabletop\Tables\Tokens\Models\TableTokenVisibility;
use PHPUnit\Framework\TestCase;

final class TabletopChamberTest extends TestCase
{
    private ChamberTables $tables;
    private ChamberMembers $members;
    private ChamberScenes $scenes;
    private ChamberTokens $tokens;
    private ChamberEncounters $encounters;
    private ChamberVitality $vitality;
    private ChamberDeathSaves $deathSaves;
    private ChamberConditions $conditions;
    private ChamberFog $fog;
    private TabletopChamber $chamber;

    protected function setUp(): void
    {
        $now = new DateTimeImmutable(
            '2026-08-26T10:00:00+01:00'
        );

        $this->tables = new ChamberTables();
        $this->members = new ChamberMembers();
        $this->scenes = new ChamberScenes();
        $this->tokens = new ChamberTokens();
        $this->encounters = new ChamberEncounters();
        $this->vitality = new ChamberVitality();
        $this->deathSaves = new ChamberDeathSaves();
        $this->conditions = new ChamberConditions();
        $this->fog = new ChamberFog();

        $table = Table::prepare(
            'table-1',
            42,
            'The Giggling Gourd',
            $now
        );

        $this->tables->save($table);

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
        $this->members->save($player);

        $scene = TableScene::create(
            'scene-1',
            'table-1',
            'Cold Vault',
            17,
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
                'token-visible',
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
                $now
            )
        );

        $this->tokens->save(
            TableToken::create(
                'token-hidden',
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
                TableTokenVisibility::HIDDEN,
                $now
            )
        );

        $this->chamber = new TabletopChamber(
            $this->tables,
            $this->members,
            $this->scenes,
            $this->tokens,
            $this->encounters,
            $this->vitality,
            $this->deathSaves,
            $this->conditions,
            null,
            null,
            null,
            null,
            $this->fog,
            new \GreatMarketrealmTabletop\Tabletop\Fog\Services\FogOfWarProjector()
        );
    }

    public function testDungeonMasterReceivesActiveSceneAndAllTokens(): void
    {
        $state = $this->chamber->state(
            'table-1',
            42
        );

        self::assertTrue($state->isDungeonMaster());
        self::assertSame(
            'Cold Vault',
            $state->scene()['name'] ?? null
        );
        self::assertCount(2, $state->tokens());
        self::assertCount(2, $state->members());
    }

    public function testPlayerCannotSeeHiddenDungeonMasterTokens(): void
    {
        $state = $this->chamber->state(
            'table-1',
            84
        );

        self::assertFalse($state->isDungeonMaster());
        self::assertCount(1, $state->tokens());
        self::assertSame(
            'Sir Pie',
            $state->tokens()[0]['label']
        );
    }

    public function testNonMemberCannotEnterChamber(): void
    {
        $this->expectException(
            TabletopAccessDenied::class
        );

        $this->chamber->state(
            'table-1',
            999
        );
    }

    public function testInvitedButInactivePlayerCannotEnterChamber(): void
    {
        $invited = TableMember::invitePlayer(
            'table-1',
            126,
            new DateTimeImmutable()
        );
        $this->members->save($invited);

        $this->expectException(
            TabletopAccessDenied::class
        );

        $this->chamber->state(
            'table-1',
            126
        );
    }

    public function testChamberSupportsTableWithoutActiveScene(): void
    {
        $this->scenes->items['table-1']
            ['scene-1']->deactivate();

        $state = $this->chamber->state(
            'table-1',
            42
        );

        self::assertNull($state->scene());
        self::assertSame([], $state->tokens());
    }

    public function testChamberProjectsVitalityForVisibleTokens(): void
    {
        $state = $this->chamber->state(
            'table-1',
            42
        );

        self::assertArrayHasKey(
            'token-visible',
            $state->vitality()
        );
        self::assertSame(
            10,
            $state->vitality()['token-visible']['maximum_hp']
        );
    }


    public function testChamberProjectsDeathSaveStateForVisibleTokens(): void
    {
        $state = $this->chamber->state('table-1', 42);

        self::assertArrayHasKey('token-visible', $state->deathSaves());
        self::assertSame(
            0,
            $state->deathSaves()['token-visible']['failures']
        );
    }



    public function testChamberProjectsConditionsForVisibleTokens(): void
    {
        $this->conditions->save(
            'table-1',
            new \GreatMarketrealmTabletop\Tabletop\Conditions\Models\TokenCondition(
                'token-visible',
                'poisoned',
                2,
                new DateTimeImmutable(
                    '2026-08-26T10:00:00+01:00'
                )
            )
        );

        $state = $this->chamber->state(
            'table-1',
            42
        );

        self::assertSame(
            'poisoned',
            $state->conditions()
                ['token-visible'][0]['condition']
        );
        self::assertSame(
            2,
            $state->conditions()
                ['token-visible'][0]['turns_remaining']
        );
    }

    public function testPlayerFogUsesCanonicalCharacterTokensAsVisionSources(): void
    {
        $now = new DateTimeImmutable('2026-08-27T12:00:00+01:00');
        $hiddenCharacter = TableToken::create(
            'token-hidden-character',
            'table-1',
            'scene-1',
            'Auby',
            TableTokenType::CHARACTER,
            'gmrt-test:auby',
            42,
            .50,
            .50,
            1,
            1,
            TableTokenVisibility::HIDDEN,
            $now
        );
        $this->tokens->save($hiddenCharacter);

        $fog = new \GreatMarketrealmTabletop\Tabletop\Fog\Models\FogOfWarState(
            'scene-1',
            true,
            []
        );
        $this->fog->save('table-1', $fog);

        $state = $this->chamber->state('table-1', 84);

        self::assertContains(
            '16:9',
            $state->fog()['visible'] ?? []
        );
        self::assertNotContains(
            'token-hidden-character',
            array_column($state->tokens(), 'id')
        );
    }

}
