<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Encounters\Services;

require_once __DIR__ . '/EncounterTestDoubles.php';

use DateTimeImmutable;
use GreatMarketrealmTabletop\Tabletop\Encounters\Exceptions\EncounterControlDenied;
use GreatMarketrealmTabletop\Tabletop\Encounters\Exceptions\StaleEncounterRevision;
use GreatMarketrealmTabletop\Tabletop\Encounters\Models\EncounterStatus;
use GreatMarketrealmTabletop\Tabletop\Encounters\Services\EncounterControlPolicy;
use GreatMarketrealmTabletop\Tabletop\Encounters\Services\EncounterManager;
use GreatMarketrealmTabletop\Tables\Memberships\Models\TableMember;
use GreatMarketrealmTabletop\Tables\Models\Table;
use GreatMarketrealmTabletop\Tables\Scenes\Models\GridType;
use GreatMarketrealmTabletop\Tables\Scenes\Models\TableScene;
use GreatMarketrealmTabletop\Tables\Tokens\Models\TableToken;
use GreatMarketrealmTabletop\Tables\Tokens\Models\TableTokenType;
use GreatMarketrealmTabletop\Tables\Tokens\Models\TableTokenVisibility;
use PHPUnit\Framework\TestCase;

final class EncounterManagerTest extends TestCase
{
    private EncounterTables $tables;
    private EncounterMembers $members;
    private EncounterScenes $scenes;
    private EncounterTokens $tokens;
    private EncounterStore $encounters;
    private EncounterManager $manager;

    protected function setUp(): void
    {
        $now = new DateTimeImmutable('2026-08-26T10:00:00+01:00');

        $this->tables = new EncounterTables();
        $this->members = new EncounterMembers();
        $this->scenes = new EncounterScenes();
        $this->tokens = new EncounterTokens();
        $this->encounters = new EncounterStore();

        $this->tables->save(Table::prepare('table-1', 42, 'Battle Table', $now));
        $this->members->save(TableMember::dungeonMaster('table-1', 42, $now));

        $player = TableMember::invitePlayer('table-1', 84, $now);
        $player->join($now);
        $this->members->save($player);

        $scene = TableScene::create(
            'scene-1', 'table-1', 'Cold Vault', 1, 1600, 900,
            GridType::SQUARE, 50, $now
        );
        $scene->activate();
        $this->scenes->save($scene);

        $this->tokens->save(TableToken::create(
            'token-a', 'table-1', 'scene-1', 'Auby',
            TableTokenType::CHARACTER, 'gmrc-character-auby', 84,
            .2, .2, 1, 1, TableTokenVisibility::VISIBLE, $now
        ));

        $this->manager = new EncounterManager(
            $this->tables,
            $this->members,
            $this->scenes,
            $this->tokens,
            $this->encounters,
            new EncounterIds(),
            new EncounterClock($now),
            new EncounterControlPolicy()
        );
    }

    public function testDungeonMasterMayPrepareAndStartEncounter(): void
    {
        $encounter = $this->manager->prepare(
            'table-1', 42, 'scene-1', 'Pantry Ambush'
        );

        $encounter = $this->manager->addCombatant(
            'table-1', 42, $encounter->id(), 'token-a', 18, 3,
            $encounter->revision()
        );

        $encounter = $this->manager->start(
            'table-1', 42, $encounter->id(), $encounter->revision()
        );

        self::assertSame(EncounterStatus::ACTIVE, $encounter->status());
        self::assertSame(1, $encounter->round());
        self::assertSame('token-a', $encounter->currentCombatant()?->tokenId());
    }

    public function testPlayerCannotControlEncounter(): void
    {
        $this->expectException(EncounterControlDenied::class);

        $this->manager->prepare(
            'table-1', 84, 'scene-1', 'Absolutely Not'
        );
    }

    public function testStaleEncounterRevisionIsRejected(): void
    {
        $encounter = $this->manager->prepare(
            'table-1', 42, 'scene-1', 'Pantry Ambush'
        );

        $this->manager->addCombatant(
            'table-1', 42, $encounter->id(), 'token-a', 18, 3,
            $encounter->revision()
        );

        $this->expectException(StaleEncounterRevision::class);

        $this->manager->start(
            'table-1', 42, $encounter->id(), 1
        );
    }

    public function testEndedTableClosesCurrentEncounterProjection(): void
    {
        $encounter = $this->manager->prepare(
            'table-1', 42, 'scene-1', 'Pantry Ambush'
        );

        $table = $this->tables->find('table-1');
        self::assertNotNull($table);
        $table->activate(new DateTimeImmutable('2026-08-26T10:01:00+01:00'));
        $table->end(new DateTimeImmutable('2026-08-26T11:00:00+01:00'));
        $this->tables->save($table);

        $current = $this->manager->currentForActiveScene('table-1');

        self::assertNotNull($current);
        self::assertTrue($current->isEnded());
    }

    public function testDungeonMasterMayBeginFreshEncounterInOneOperation(): void
    {
        $encounter = $this->manager->begin(
            'table-1',
            42,
            'Pantry Ambush',
            [[
                'token_id' => 'token-a',
                'initiative' => 17,
                'initiative_modifier' => 3,
            ]]
        );

        self::assertSame(EncounterStatus::ACTIVE, $encounter->status());
        self::assertSame(1, $encounter->round());
        self::assertSame('token-a', $encounter->currentCombatant()?->tokenId());
        self::assertSame($encounter->id(), $this->encounters->currentForScene('table-1', 'scene-1')?->id());
    }

    public function testPlayerCannotBeginEncounterFromExploration(): void
    {
        $this->expectException(EncounterControlDenied::class);

        $this->manager->begin(
            'table-1',
            84,
            'Absolutely Not',
            [['token_id' => 'token-a', 'initiative' => 10]]
        );
    }

    public function testEndedEncounterReturnsSceneToExplorationAndAllowsFreshBattle(): void
    {
        $first = $this->manager->begin(
            'table-1', 42, 'First Battle',
            [['token_id' => 'token-a', 'initiative' => 10]]
        );
        $this->manager->end('table-1', 42, $first->id(), $first->revision());

        self::assertNull($this->encounters->currentForScene('table-1', 'scene-1'));

        $second = $this->manager->begin(
            'table-1', 42, 'Second Battle',
            [['token_id' => 'token-a', 'initiative' => 12]]
        );

        self::assertSame(EncounterStatus::ACTIVE, $second->status());
        self::assertSame(1, $second->round());
    }

}
