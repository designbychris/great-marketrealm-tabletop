<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tables\Scenes\Repositories;

use GreatMarketrealmTabletop\Tables\Scenes\Contracts\TableSceneRepository;
use GreatMarketrealmTabletop\Tables\Scenes\Models\TableScene;

defined('ABSPATH') || exit;

final class WordPressTableSceneRepository implements TableSceneRepository
{
    private const OPTION = 'gmrt_table_scenes';

    /** @return array<int,TableScene> */
    public function forTable(string $tableId): array
    {
        $scenes = [];

        foreach ($this->records()[$tableId] ?? [] as $record) {
            if (is_array($record)) {
                $scenes[] = TableScene::reconstitute($record);
            }
        }

        return $scenes;
    }

    public function find(string $tableId, string $sceneId): ?TableScene
    {
        $record = $this->records()[$tableId][$sceneId] ?? null;

        return is_array($record)
            ? TableScene::reconstitute($record)
            : null;
    }

    public function save(TableScene $scene): void
    {
        $records = $this->records();
        $records[$scene->tableId()][$scene->id()] = $scene->toArray();

        update_option(self::OPTION, $records, false);
    }

    /** @return array<string,array<string,array<string,mixed>>> */
    private function records(): array
    {
        $records = get_option(self::OPTION, []);
        return is_array($records) ? $records : [];
    }
}
