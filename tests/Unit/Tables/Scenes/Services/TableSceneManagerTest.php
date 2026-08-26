<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tables\Scenes\Services;

require_once __DIR__ . '/SceneTestDoubles.php';

use DateTimeImmutable;
use GreatMarketrealmTabletop\Tables\Models\Table;
use GreatMarketrealmTabletop\Tables\Scenes\Exceptions\TableSceneException;
use GreatMarketrealmTabletop\Tables\Scenes\Models\GridType;
use GreatMarketrealmTabletop\Tables\Scenes\Services\TableSceneManager;
use PHPUnit\Framework\TestCase;

final class TableSceneManagerTest extends TestCase
{
    private SceneTableRepository $tables;
    private SceneRepository $scenes;
    private TableSceneManager $manager;

    protected function setUp(): void
    {
        $this->tables = new SceneTableRepository();
        $this->scenes = new SceneRepository();
        $this->tables->save(Table::prepare(
            'table-1', 5, 'Friday Feast',
            new DateTimeImmutable('2026-08-26T10:00:00+01:00')
        ));
        $this->manager = new TableSceneManager(
            $this->tables, $this->scenes, new SceneIds(),
            new SceneClock(new DateTimeImmutable('2026-08-26T10:01:00+01:00'))
        );
    }

    public function testDungeonMasterMayPrepareSeveralPersistentScenes(): void
    {
        $a = $this->create('Cold Vault', 11);
        $b = $this->create('Mould Lair', 12);

        self::assertSame('scene-1', $a->id());
        self::assertSame('scene-2', $b->id());
        self::assertCount(2, $this->manager->scenes('table-1'));
    }

    public function testExactlyOneSceneIsActiveAfterSwitching(): void
    {
        $a = $this->create('Cold Vault', 11);
        $b = $this->create('Mould Lair', 12);

        $this->manager->activate('table-1', $a->id());
        self::assertSame($a->id(), $this->manager->active('table-1')?->id());

        $this->manager->activate('table-1', $b->id());
        self::assertSame($b->id(), $this->manager->active('table-1')?->id());

        $active = array_filter(
            $this->manager->scenes('table-1'),
            fn($scene) => $scene->isActive()
        );
        self::assertCount(1, $active);
    }

    public function testEndedTableRetainsButCannotChangeScenes(): void
    {
        $scene = $this->create('Cold Vault', 11);
        $table = $this->tables->find('table-1');
        $table->activate(new DateTimeImmutable('2026-08-26T10:02:00+01:00'));
        $table->end(new DateTimeImmutable('2026-08-26T11:00:00+01:00'));
        $this->tables->save($table);

        self::assertCount(1, $this->manager->scenes('table-1'));

        $this->expectException(TableSceneException::class);
        $this->manager->activate('table-1', $scene->id());
    }

    public function testSceneFromAnotherTableCannotBeActivated(): void
    {
        $this->tables->save(Table::prepare(
            'table-2', 6, 'Other Table', new DateTimeImmutable()
        ));
        $scene = $this->create('Cold Vault', 11);

        $this->expectException(TableSceneException::class);
        $this->manager->activate('table-2', $scene->id());
    }

    private function create(string $name, int $attachment): \GreatMarketrealmTabletop\Tables\Scenes\Models\TableScene
    {
        return $this->manager->create(
            'table-1', $name, $attachment, 1600, 900,
            GridType::SQUARE, 50
        );
    }
}
